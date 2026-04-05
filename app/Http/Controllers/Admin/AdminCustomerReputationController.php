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
        $suspiciousRatings = $this->loadSuspiciousRatings();
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
            'suspicious_pending' => $suspiciousRatings->whereNull('admin_reviewed_at')->count(),
            'suspicious_reviewed' => $suspiciousRatings->whereNotNull('admin_reviewed_at')->count(),
        ];

        return view('admin.customer_reputation', compact(
            'customers',
            'history',
            'suspiciousRatings',
            'summary',
            'topCustomers',
            'problematicCustomers',
            'search',
            'risk',
            'sort'
        ));
    }

    public function reviewRating(Request $request, int $id)
    {
        if (!$this->supportsAdminReview()) {
            return back()->withErrors([
                'customer_reputation' => 'Customer rating review fields are not available yet.',
            ]);
        }

        $request->validate([
            'action' => 'required|in:review,reopen',
            'admin_review_note' => 'nullable|string|max:1200',
        ]);

        $rating = DB::table('customer_ratings')
            ->where('id', $id)
            ->first();

        if (!$rating) {
            return back()->withErrors([
                'customer_reputation' => 'Customer rating record not found.',
            ]);
        }

        $action = (string) $request->input('action');
        $note = trim((string) $request->input('admin_review_note', '')) ?: null;
        $timestamp = now();

        if ($action === 'review') {
            DB::table('customer_ratings')
                ->where('id', $id)
                ->update([
                    'admin_reviewed_at' => $timestamp,
                    'admin_reviewed_by' => session('admin_id'),
                    'admin_review_note' => $note,
                    'updated_at' => $timestamp,
                ]);

            $this->logRatingActivity(
                $id,
                (int) $rating->booking_id,
                (int) $rating->customer_id,
                (int) $rating->provider_id,
                'admin_reviewed',
                [
                    'admin_review_note' => $note,
                    'admin_reviewed_at' => $timestamp->toDateTimeString(),
                    'admin_reviewed_by' => session('admin_id'),
                ]
            );

            return back()->with('success', 'Suspicious customer rating marked as reviewed.');
        }

        DB::table('customer_ratings')
            ->where('id', $id)
            ->update([
                'admin_reviewed_at' => null,
                'admin_reviewed_by' => null,
                'admin_review_note' => null,
                'updated_at' => $timestamp,
            ]);

        $this->logRatingActivity(
            $id,
            (int) $rating->booking_id,
            (int) $rating->customer_id,
            (int) $rating->provider_id,
            'admin_review_reopened',
            [
                'admin_review_note' => $note,
                'admin_reviewed_by' => session('admin_id'),
            ]
        );

        return back()->with('success', 'Suspicious customer rating moved back to pending review.');
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

    private function loadSuspiciousRatings()
    {
        $areasSub = $this->bookingAreasSubquery();
        $supportsAdminReview = $this->supportsAdminReview();
        $query = DB::table('customer_ratings as cr')
            ->join('customers as c', 'c.id', '=', 'cr.customer_id')
            ->join('service_providers as p', 'p.id', '=', 'cr.provider_id')
            ->leftJoin('bookings as b', 'b.id', '=', 'cr.booking_id')
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->limit(200)
            ->select(
                'cr.id',
                'cr.customer_id',
                'cr.booking_id',
                'cr.rating',
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
                'cr.created_at',
                'b.reference_code',
                'b.booking_date',
                's.name as service_name',
                DB::raw("COALESCE(areas.areas_label, o.label) as option_name"),
                'c.name as customer_name',
                'c.email as customer_email',
                DB::raw("TRIM(CONCAT(COALESCE(p.first_name,''), ' ', COALESCE(p.last_name,''))) as provider_name")
            );

        if ($supportsAdminReview && Schema::hasTable('admins')) {
            $query->leftJoin('admins as reviewed_admin', 'reviewed_admin.id', '=', 'cr.admin_reviewed_by');
            $query->addSelect(
                'cr.admin_reviewed_at',
                'cr.admin_reviewed_by',
                'cr.admin_review_note',
                DB::raw("COALESCE(reviewed_admin.name, 'Admin') as admin_reviewed_by_name")
            );
        } else {
            $query->addSelect(
                DB::raw('NULL as admin_reviewed_at'),
                DB::raw('NULL as admin_reviewed_by'),
                DB::raw('NULL as admin_review_note'),
                DB::raw('NULL as admin_reviewed_by_name')
            );
        }

        return $query
            ->get()
            ->map(function ($row) {
                $flags = collect([
                    !empty($row->flag_understated_area) ? 'Understated area' : null,
                    !empty($row->flag_hidden_sections) ? 'Hidden sections' : null,
                    !empty($row->flag_misleading_request) ? 'Misleading request' : null,
                    !empty($row->flag_difficult_behavior) ? 'Difficult behavior' : null,
                    !empty($row->flag_payment_issue) ? 'Payment issue' : null,
                    !empty($row->flag_last_minute_changes) ? 'Last-minute changes' : null,
                    !empty($row->unexpected_extra_work) ? 'Unexpected extra work' : null,
                ])->filter()->values()->all();

                $row->suspicion_flags = $flags;
                $row->negative_flags_count = count($flags);
                $row->is_reviewed = !empty($row->admin_reviewed_at);
                $row->suspicion_score =
                    ($row->negative_flags_count * 3) +
                    ((int) $row->rating <= 2 ? 3 : 0) +
                    (!empty($row->attachment_path) ? 1 : 0) +
                    ((int) ($row->edit_count ?? 0) > 0 ? 1 : 0);

                return $row;
            })
            ->filter(function ($row) {
                return $row->negative_flags_count > 0 || (int) $row->rating <= 2;
            })
            ->sortByDesc(function ($row) {
                return
                    (($row->is_reviewed ? 0 : 1) * 1000000000000) +
                    ($row->suspicion_score * 1000000000) +
                    strtotime((string) $row->created_at);
            })
            ->take(8)
            ->values();
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

    private function supportsAdminReview(): bool
    {
        return Schema::hasTable('customer_ratings')
            && Schema::hasColumn('customer_ratings', 'admin_reviewed_at')
            && Schema::hasColumn('customer_ratings', 'admin_reviewed_by')
            && Schema::hasColumn('customer_ratings', 'admin_review_note');
    }

    private function logRatingActivity(
        int $customerRatingId,
        int $bookingId,
        int $customerId,
        int $providerId,
        string $action,
        array $payload
    ): void {
        if (!Schema::hasTable('customer_rating_logs')) {
            return;
        }

        DB::table('customer_rating_logs')->insert([
            'customer_rating_id' => $customerRatingId,
            'booking_id' => $bookingId,
            'customer_id' => $customerId,
            'provider_id' => $providerId,
            'actor_role' => 'admin',
            'actor_id' => session('admin_id'),
            'action' => $action,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
