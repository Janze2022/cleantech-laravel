<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminCustomerReputationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $risk = trim((string) $request->query('risk', 'all'));
        $sort = trim((string) $request->query('sort', 'problematic'));

        $bookingStats = DB::table('bookings')
            ->selectRaw("
                customer_id,
                COUNT(*) as total_bookings,
                SUM(CASE WHEN LOWER(status) IN ('completed', 'paid') THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN LOWER(status) = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
            ")
            ->groupBy('customer_id');

        $ratingStats = DB::table('customer_ratings')
            ->selectRaw("
                customer_id,
                AVG(rating) as avg_rating,
                COUNT(*) as rating_count,
                SUM(CASE WHEN flag_understated_area = 1 OR flag_hidden_sections = 1 OR flag_misleading_request = 1 THEN 1 ELSE 0 END) as rating_mismatch_count,
                SUM(CASE WHEN flag_difficult_behavior = 1 OR flag_payment_issue = 1 OR flag_last_minute_changes = 1 OR unexpected_extra_work = 1 THEN 1 ELSE 0 END) as behavior_issue_count,
                SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as low_rating_count,
                MAX(created_at) as last_rated_at
            ")
            ->groupBy('customer_id');

        $adjustmentStats = null;
        if (Schema::hasTable('booking_adjustments')) {
            $adjustmentStats = DB::table('booking_adjustments')
                ->selectRaw('customer_id, COUNT(*) as adjustment_mismatch_count')
                ->groupBy('customer_id');
        }

        $query = DB::table('customers as c')
            ->leftJoinSub($bookingStats, 'bs', function ($join) {
                $join->on('bs.customer_id', '=', 'c.id');
            })
            ->leftJoinSub($ratingStats, 'rs', function ($join) {
                $join->on('rs.customer_id', '=', 'c.id');
            })
            ->select(
                'c.id',
                'c.name',
                'c.email',
                Schema::hasColumn('customers', 'phone')
                    ? 'c.phone'
                    : DB::raw('NULL as phone'),
                Schema::hasColumn('customers', 'profile_image')
                    ? 'c.profile_image'
                    : DB::raw('NULL as profile_image'),
                DB::raw('COALESCE(bs.total_bookings, 0) as total_bookings'),
                DB::raw('COALESCE(bs.completed_bookings, 0) as completed_bookings'),
                DB::raw('COALESCE(bs.cancelled_bookings, 0) as cancelled_bookings'),
                DB::raw('COALESCE(rs.avg_rating, 0) as avg_rating'),
                DB::raw('COALESCE(rs.rating_count, 0) as rating_count'),
                DB::raw('COALESCE(rs.rating_mismatch_count, 0) as rating_mismatch_count'),
                DB::raw('COALESCE(rs.behavior_issue_count, 0) as behavior_issue_count'),
                DB::raw('COALESCE(rs.low_rating_count, 0) as low_rating_count'),
                'rs.last_rated_at'
            );

        if ($adjustmentStats) {
            $query->leftJoinSub($adjustmentStats, 'adj', function ($join) {
                $join->on('adj.customer_id', '=', 'c.id');
            });

            $query->addSelect(DB::raw('COALESCE(adj.adjustment_mismatch_count, 0) as adjustment_mismatch_count'));
        } else {
            $query->addSelect(DB::raw('0 as adjustment_mismatch_count'));
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('c.name', 'like', '%' . $search . '%')
                    ->orWhere('c.email', 'like', '%' . $search . '%');

                if (Schema::hasColumn('customers', 'phone')) {
                    $sub->orWhere('c.phone', 'like', '%' . $search . '%');
                }
            });
        }

        $customers = $query
            ->orderBy('c.name')
            ->get()
            ->map(function ($row) {
                $row->avg_rating = round((float) $row->avg_rating, 1);
                $row->total_bookings = (int) $row->total_bookings;
                $row->completed_bookings = (int) $row->completed_bookings;
                $row->cancelled_bookings = (int) $row->cancelled_bookings;
                $row->rating_count = (int) $row->rating_count;
                $row->rating_mismatch_count = (int) $row->rating_mismatch_count;
                $row->adjustment_mismatch_count = (int) $row->adjustment_mismatch_count;
                $row->behavior_issue_count = (int) $row->behavior_issue_count;
                $row->low_rating_count = (int) $row->low_rating_count;
                $row->mismatch_count = $row->rating_mismatch_count + $row->adjustment_mismatch_count;
                $row->complaint_count = $row->behavior_issue_count + $row->low_rating_count;
                $row->success_rate = $row->total_bookings > 0
                    ? round(($row->completed_bookings / $row->total_bookings) * 100)
                    : 0;
                $row->reputation_score = $this->reputationScore($row);
                $row->risk_level = $this->riskLevel($row);
                $row->problem_index = ($row->mismatch_count * 3) + ($row->complaint_count * 4) + $row->cancelled_bookings;

                return $row;
            });

        if ($risk !== 'all') {
            $customers = $customers->filter(function ($row) use ($risk) {
                return strtolower($row->risk_level) === strtolower($risk);
            })->values();
        }

        $customers = $this->sortCustomers($customers, $sort)->values();

        $history = $this->loadRatingHistory($customers->pluck('id')->all());
        $topCustomers = $customers->sortByDesc(function ($row) {
            return ($row->reputation_score * 1000) + ($row->completed_bookings * 10) + $row->rating_count;
        })->take(5)->values();
        $problematicCustomers = $customers->sortByDesc(function ($row) {
            return ($row->problem_index * 1000) + max(0, 100 - $row->reputation_score);
        })->take(5)->values();

        $summary = (object) [
            'customers' => $customers->count(),
            'rated_customers' => $customers->where('rating_count', '>', 0)->count(),
            'avg_rating' => $customers->where('rating_count', '>', 0)->avg('avg_rating') ?? 0,
            'high_risk' => $customers->where('risk_level', 'High')->count(),
            'mismatches' => $customers->sum('mismatch_count'),
            'complaints' => $customers->sum('complaint_count'),
        ];

        return view('admin.customer_reputation', compact(
            'customers',
            'history',
            'summary',
            'topCustomers',
            'problematicCustomers',
            'search',
            'risk',
            'sort'
        ));
    }

    private function loadRatingHistory(array $customerIds)
    {
        if (empty($customerIds)) {
            return collect();
        }

        $areasSub = $this->bookingAreasSubquery();

        $rows = DB::table('customer_ratings as cr')
            ->join('service_providers as p', 'p.id', '=', 'cr.provider_id')
            ->leftJoin('bookings as b', 'b.id', '=', 'cr.booking_id')
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->whereIn('cr.customer_id', $customerIds)
            ->orderByDesc('cr.created_at')
            ->select(
                'cr.id',
                'cr.customer_id',
                'cr.booking_id',
                'cr.rating',
                'cr.booking_details_accurate',
                'cr.respectful',
                'cr.easy_to_communicate',
                'cr.paid_reliably',
                'cr.unexpected_extra_work',
                'cr.flag_understated_area',
                'cr.flag_hidden_sections',
                'cr.flag_misleading_request',
                'cr.flag_difficult_behavior',
                'cr.flag_payment_issue',
                'cr.flag_last_minute_changes',
                'cr.comment',
                'cr.attachment_path',
                'cr.attachment_name',
                'cr.attachment_mime',
                'cr.edit_count',
                'cr.editable_until',
                'cr.created_at',
                'cr.updated_at',
                'b.reference_code',
                'b.status as booking_status',
                'b.booking_date',
                's.name as service_name',
                DB::raw("COALESCE(areas.areas_label, o.label) as option_name"),
                DB::raw("TRIM(CONCAT(COALESCE(p.first_name,''), ' ', COALESCE(p.last_name,''))) as provider_name")
            )
            ->get()
            ->groupBy('customer_id');

        return $rows;
    }

    private function reputationScore(object $row): int
    {
        $score = 100;

        if ($row->rating_count > 0) {
            $score -= max(0, (5 - $row->avg_rating) * 12);
        }

        $score -= min($row->mismatch_count * 12, 36);
        $score -= min($row->complaint_count * 10, 40);
        $score -= min($row->cancelled_bookings * 4, 24);
        $score += min($row->completed_bookings * 2, 20);

        return (int) max(0, min(100, round($score)));
    }

    private function riskLevel(object $row): string
    {
        if (
            $row->complaint_count >= 3 ||
            $row->mismatch_count >= 2 ||
            ($row->rating_count > 0 && $row->avg_rating < 3.2) ||
            $row->cancelled_bookings >= 4
        ) {
            return 'High';
        }

        if (
            $row->complaint_count >= 1 ||
            $row->mismatch_count >= 1 ||
            ($row->rating_count > 0 && $row->avg_rating < 4.0) ||
            $row->cancelled_bookings >= 2
        ) {
            return 'Medium';
        }

        return 'Low';
    }

    private function sortCustomers($customers, string $sort)
    {
        return match ($sort) {
            'top' => $customers->sortByDesc(function ($row) {
                return ($row->reputation_score * 1000) + ($row->completed_bookings * 10);
            }),
            'highest' => $customers->sortByDesc(function ($row) {
                return ($row->avg_rating * 1000) + $row->rating_count;
            }),
            'lowest' => $customers->sortBy(function ($row) {
                return ($row->avg_rating * 1000) - $row->rating_count;
            }),
            'completed' => $customers->sortByDesc('completed_bookings'),
            'cancelled' => $customers->sortByDesc('cancelled_bookings'),
            default => $customers->sortByDesc(function ($row) {
                return ($row->problem_index * 1000) + max(0, 100 - $row->reputation_score);
            }),
        };
    }

    private function bookingAreasSubquery()
    {
        if (!Schema::hasTable('booking_service_options') || !Schema::hasTable('service_options')) {
            return DB::table('bookings as b_fallback')
                ->selectRaw('NULL as booking_id, NULL as areas_label')
                ->whereRaw('1 = 0');
        }

        return DB::table('booking_service_options as bso')
            ->join('service_options as so2', 'so2.id', '=', 'bso.service_option_id')
            ->selectRaw("bso.booking_id, GROUP_CONCAT(so2.label ORDER BY so2.label SEPARATOR ', ') as areas_label")
            ->groupBy('bso.booking_id');
    }
}
