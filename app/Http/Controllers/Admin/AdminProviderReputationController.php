<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminProviderReputationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $risk = trim((string) $request->query('risk', 'all'));
        $sort = trim((string) $request->query('sort', 'attention'));

        if (!Schema::hasTable('service_providers')) {
            return $this->renderEmptyState($search, $risk, $sort);
        }

        $providerNameExpr = $this->providerNameExpression('sp');
        $providerFallbackExpr = $this->providerLabelExpression('sp.id');
        $bookingStats = $this->bookingStatsSubquery();
        $reviewStats = $this->reviewStatsSubquery();

        $query = DB::table('service_providers as sp')
            ->select(
                'sp.id',
                DB::raw("COALESCE(NULLIF(TRIM($providerNameExpr), ''), $providerFallbackExpr) as name"),
                Schema::hasColumn('service_providers', 'email')
                    ? 'sp.email'
                    : DB::raw('NULL as email'),
                Schema::hasColumn('service_providers', 'phone')
                    ? 'sp.phone'
                    : DB::raw('NULL as phone'),
                Schema::hasColumn('service_providers', 'status')
                    ? 'sp.status'
                    : DB::raw('NULL as status')
            );

        if ($bookingStats) {
            $query->leftJoinSub($bookingStats, 'bs', function ($join) {
                $join->on('bs.provider_id', '=', 'sp.id');
            });

            $query->addSelect(
                DB::raw('COALESCE(bs.total_bookings, 0) as total_bookings'),
                DB::raw('COALESCE(bs.completed_bookings, 0) as completed_bookings'),
                DB::raw('COALESCE(bs.cancelled_bookings, 0) as cancelled_bookings'),
                'bs.last_booking_date'
            );
        } else {
            $query->addSelect(
                DB::raw('0 as total_bookings'),
                DB::raw('0 as completed_bookings'),
                DB::raw('0 as cancelled_bookings'),
                DB::raw('NULL as last_booking_date')
            );
        }

        if ($reviewStats) {
            $query->leftJoinSub($reviewStats, 'rs', function ($join) {
                $join->on('rs.provider_id', '=', 'sp.id');
            });

            $query->addSelect(
                DB::raw('COALESCE(rs.avg_rating, 0) as avg_rating'),
                DB::raw('COALESCE(rs.rating_count, 0) as rating_count'),
                DB::raw('COALESCE(rs.low_rating_count, 0) as low_rating_count'),
                'rs.last_reviewed_at'
            );
        } else {
            $query->addSelect(
                DB::raw('0 as avg_rating'),
                DB::raw('0 as rating_count'),
                DB::raw('0 as low_rating_count'),
                DB::raw('NULL as last_reviewed_at')
            );
        }

        $providers = $query
            ->orderBy('sp.id')
            ->get()
            ->map(function ($row) {
                $row->name = trim((string) ($row->name ?? '')) ?: ('Provider #' . $row->id);
                $row->email = trim((string) ($row->email ?? ''));
                $row->phone = trim((string) ($row->phone ?? ''));
                $row->status = trim((string) ($row->status ?? '')) ?: 'Unknown';
                $row->avg_rating = round((float) ($row->avg_rating ?? 0), 1);
                $row->total_bookings = (int) ($row->total_bookings ?? 0);
                $row->completed_bookings = (int) ($row->completed_bookings ?? 0);
                $row->cancelled_bookings = (int) ($row->cancelled_bookings ?? 0);
                $row->rating_count = (int) ($row->rating_count ?? 0);
                $row->low_rating_count = (int) ($row->low_rating_count ?? 0);
                $row->success_rate = $row->total_bookings > 0
                    ? (int) round(($row->completed_bookings / $row->total_bookings) * 100)
                    : 0;
                $row->cancellation_rate = $row->total_bookings > 0
                    ? (int) round(($row->cancelled_bookings / $row->total_bookings) * 100)
                    : 0;
                $row->attention_index = ($row->low_rating_count * 5) + ($row->cancelled_bookings * 3) + max(0, 85 - $row->success_rate);
                $row->reputation_score = $this->reputationScore($row);
                $row->risk_level = $this->riskLevel($row);

                return $row;
            })
            ->filter(function ($row) use ($search) {
                if ($search === '') {
                    return true;
                }

                $needle = strtolower($search);
                $haystacks = [
                    strtolower((string) $row->name),
                    strtolower((string) $row->email),
                    strtolower((string) $row->phone),
                    strtolower((string) $row->status),
                ];

                foreach ($haystacks as $haystack) {
                    if ($haystack !== '' && str_contains($haystack, $needle)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();

        if ($risk !== 'all') {
            $providers = $providers->filter(function ($row) use ($risk) {
                return strtolower((string) $row->risk_level) === strtolower($risk);
            })->values();
        }

        $providers = $this->sortProviders($providers, $sort)->values();

        $history = $this->loadReviewHistory($providers->pluck('id')->all());
        $recentReviews = $this->loadRecentReviews();
        $topProviders = $providers->sortByDesc(function ($row) {
            return ($row->reputation_score * 1000) + ($row->completed_bookings * 10) + $row->rating_count;
        })->take(5)->values();
        $attentionProviders = $providers->sortByDesc(function ($row) {
            return ($row->attention_index * 1000) + max(0, 100 - $row->reputation_score);
        })->take(5)->values();

        $summary = (object) [
            'providers' => $providers->count(),
            'rated_providers' => $providers->where('rating_count', '>', 0)->count(),
            'avg_rating' => $providers->where('rating_count', '>', 0)->avg('avg_rating') ?? 0,
            'high_risk' => $providers->where('risk_level', 'High')->count(),
            'completed_jobs' => $providers->sum('completed_bookings'),
            'low_ratings' => $providers->sum('low_rating_count'),
        ];

        return view('admin.provider_reputation', compact(
            'providers',
            'history',
            'recentReviews',
            'summary',
            'topProviders',
            'attentionProviders',
            'search',
            'risk',
            'sort'
        ));
    }

    private function renderEmptyState(string $search, string $risk, string $sort)
    {
        return view('admin.provider_reputation', [
            'providers' => collect(),
            'history' => collect(),
            'recentReviews' => collect(),
            'summary' => (object) [
                'providers' => 0,
                'rated_providers' => 0,
                'avg_rating' => 0,
                'high_risk' => 0,
                'completed_jobs' => 0,
                'low_ratings' => 0,
            ],
            'topProviders' => collect(),
            'attentionProviders' => collect(),
            'search' => $search,
            'risk' => $risk,
            'sort' => $sort,
        ]);
    }

    private function bookingStatsSubquery()
    {
        if (!Schema::hasTable('bookings')) {
            return null;
        }

        return DB::table('bookings as b')
            ->whereNotNull('b.provider_id')
            ->selectRaw("
                b.provider_id,
                COUNT(*) as total_bookings,
                SUM(CASE WHEN LOWER(b.status) IN ('completed', 'paid') THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN LOWER(b.status) = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                MAX(b.booking_date) as last_booking_date
            ")
            ->groupBy('b.provider_id');
    }

    private function reviewStatsSubquery()
    {
        if (!Schema::hasTable('reviews')) {
            return null;
        }

        $query = DB::table('reviews as r')
            ->whereNotNull('r.provider_id')
            ->selectRaw("
                r.provider_id,
                AVG(r.rating) as avg_rating,
                COUNT(*) as rating_count,
                SUM(CASE WHEN r.rating <= 2 THEN 1 ELSE 0 END) as low_rating_count
            ")
            ->groupBy('r.provider_id');

        if (Schema::hasColumn('reviews', 'created_at')) {
            $query->addSelect(DB::raw('MAX(r.created_at) as last_reviewed_at'));
        } else {
            $query->addSelect(DB::raw('NULL as last_reviewed_at'));
        }

        return $query;
    }

    private function loadReviewHistory(array $providerIds)
    {
        if (
            empty($providerIds) ||
            !Schema::hasTable('reviews') ||
            !Schema::hasTable('customers')
        ) {
            return collect();
        }

        $areasSub = $this->bookingAreasSubquery();
        $customerNameExpr = $this->customerNameExpression('c');
        $serviceNameExpr = $this->serviceNameExpression('s');
        $optionNameExpr = $this->serviceOptionExpression('o');
        $orderColumn = Schema::hasColumn('reviews', 'created_at') ? 'r.created_at' : 'r.id';

        $query = DB::table('reviews as r')
            ->join('customers as c', 'c.id', '=', 'r.customer_id')
            ->whereIn('r.provider_id', $providerIds)
            ->orderByDesc($orderColumn);

        if (Schema::hasTable('bookings')) {
            $query->leftJoin('bookings as b', 'b.id', '=', 'r.booking_id');
        }

        if (Schema::hasTable('services')) {
            $query->leftJoin('services as s', 's.id', '=', 'b.service_id');
        }

        if (Schema::hasTable('service_options')) {
            $query->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id');
        }

        $query->leftJoinSub($areasSub, 'areas', function ($join) {
            $join->on('areas.booking_id', '=', 'b.id');
        });

        return $query
            ->select(
                'r.id',
                'r.provider_id',
                Schema::hasColumn('reviews', 'booking_id')
                    ? 'r.booking_id'
                    : DB::raw('NULL as booking_id'),
                'r.rating',
                Schema::hasColumn('reviews', 'comment')
                    ? 'r.comment'
                    : DB::raw('NULL as comment'),
                Schema::hasColumn('reviews', 'attachment_path')
                    ? 'r.attachment_path'
                    : DB::raw('NULL as attachment_path'),
                Schema::hasColumn('reviews', 'attachment_name')
                    ? 'r.attachment_name'
                    : DB::raw('NULL as attachment_name'),
                Schema::hasColumn('reviews', 'attachment_mime')
                    ? 'r.attachment_mime'
                    : DB::raw('NULL as attachment_mime'),
                Schema::hasColumn('reviews', 'created_at')
                    ? 'r.created_at as reviewed_at'
                    : DB::raw('NULL as reviewed_at'),
                Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'reference_code')
                    ? 'b.reference_code'
                    : DB::raw('NULL as reference_code'),
                Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'booking_date')
                    ? 'b.booking_date'
                    : DB::raw('NULL as booking_date'),
                DB::raw("$customerNameExpr as customer_name"),
                Schema::hasColumn('customers', 'email')
                    ? 'c.email as customer_email'
                    : DB::raw('NULL as customer_email'),
                DB::raw("$serviceNameExpr as service_name"),
                DB::raw("COALESCE(areas.areas_label, $optionNameExpr) as option_name")
            )
            ->get()
            ->groupBy('provider_id');
    }

    private function loadRecentReviews()
    {
        if (
            !Schema::hasTable('reviews') ||
            !Schema::hasTable('customers') ||
            !Schema::hasTable('service_providers')
        ) {
            return collect();
        }

        $areasSub = $this->bookingAreasSubquery();
        $customerNameExpr = $this->customerNameExpression('c');
        $providerNameExpr = $this->providerNameExpression('sp');
        $providerFallbackExpr = $this->providerLabelExpression('sp.id');
        $serviceNameExpr = $this->serviceNameExpression('s');
        $optionNameExpr = $this->serviceOptionExpression('o');
        $orderColumn = Schema::hasColumn('reviews', 'created_at') ? 'r.created_at' : 'r.id';

        $query = DB::table('reviews as r')
            ->join('customers as c', 'c.id', '=', 'r.customer_id')
            ->join('service_providers as sp', 'sp.id', '=', 'r.provider_id')
            ->orderByDesc($orderColumn)
            ->limit(200);

        if (Schema::hasTable('bookings')) {
            $query->leftJoin('bookings as b', 'b.id', '=', 'r.booking_id');
        }

        if (Schema::hasTable('services')) {
            $query->leftJoin('services as s', 's.id', '=', 'b.service_id');
        }

        if (Schema::hasTable('service_options')) {
            $query->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id');
        }

        $query->leftJoinSub($areasSub, 'areas', function ($join) {
            $join->on('areas.booking_id', '=', 'b.id');
        });

        return $query
            ->select(
                'r.id',
                'r.provider_id',
                'r.rating',
                Schema::hasColumn('reviews', 'comment')
                    ? 'r.comment'
                    : DB::raw('NULL as comment'),
                Schema::hasColumn('reviews', 'attachment_path')
                    ? 'r.attachment_path'
                    : DB::raw('NULL as attachment_path'),
                Schema::hasColumn('reviews', 'attachment_name')
                    ? 'r.attachment_name'
                    : DB::raw('NULL as attachment_name'),
                Schema::hasColumn('reviews', 'attachment_mime')
                    ? 'r.attachment_mime'
                    : DB::raw('NULL as attachment_mime'),
                Schema::hasColumn('reviews', 'created_at')
                    ? 'r.created_at as reviewed_at'
                    : DB::raw('NULL as reviewed_at'),
                Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'reference_code')
                    ? 'b.reference_code'
                    : DB::raw('NULL as reference_code'),
                Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'booking_date')
                    ? 'b.booking_date'
                    : DB::raw('NULL as booking_date'),
                DB::raw("$customerNameExpr as customer_name"),
                Schema::hasColumn('customers', 'email')
                    ? 'c.email as customer_email'
                    : DB::raw('NULL as customer_email'),
                DB::raw("COALESCE(NULLIF(TRIM($providerNameExpr), ''), $providerFallbackExpr) as provider_name"),
                Schema::hasColumn('service_providers', 'status')
                    ? 'sp.status as provider_status'
                    : DB::raw('NULL as provider_status'),
                DB::raw("$serviceNameExpr as service_name"),
                DB::raw("COALESCE(areas.areas_label, $optionNameExpr) as option_name")
            )
            ->get()
            ->sortByDesc(function ($row) {
                $timestamp = !empty($row->reviewed_at)
                    ? strtotime((string) $row->reviewed_at)
                    : (!empty($row->booking_date) ? strtotime((string) $row->booking_date) : 0);

                return (((int) $row->rating <= 2 ? 1 : 0) * 1000000000000) + $timestamp;
            })
            ->take(8)
            ->values();
    }

    private function reputationScore(object $row): int
    {
        $score = 100;

        if ($row->rating_count > 0) {
            $score -= max(0, (5 - $row->avg_rating) * 14);
        }

        $score -= min($row->low_rating_count * 12, 36);
        $score -= min($row->cancelled_bookings * 5, 30);
        $score -= max(0, 80 - $row->success_rate) * 0.4;
        $score += min($row->completed_bookings * 2, 24);

        return (int) max(0, min(100, round($score)));
    }

    private function riskLevel(object $row): string
    {
        if (
            $row->low_rating_count >= 3 ||
            ($row->rating_count > 0 && $row->avg_rating < 3.5) ||
            $row->cancelled_bookings >= 4 ||
            $row->success_rate < 60
        ) {
            return 'High';
        }

        if (
            $row->low_rating_count >= 1 ||
            ($row->rating_count > 0 && $row->avg_rating < 4.2) ||
            $row->cancelled_bookings >= 2 ||
            $row->success_rate < 80
        ) {
            return 'Medium';
        }

        return 'Low';
    }

    private function sortProviders($providers, string $sort)
    {
        return match ($sort) {
            'top' => $providers->sortByDesc(function ($row) {
                return ($row->reputation_score * 1000) + ($row->completed_bookings * 10);
            }),
            'highest' => $providers->sortByDesc(function ($row) {
                return ($row->avg_rating * 1000) + $row->rating_count;
            }),
            'lowest' => $providers->sortBy(function ($row) {
                return ($row->avg_rating * 1000) - $row->rating_count;
            }),
            'completed' => $providers->sortByDesc('completed_bookings'),
            'cancelled' => $providers->sortByDesc('cancelled_bookings'),
            default => $providers->sortByDesc(function ($row) {
                return ($row->attention_index * 1000) + max(0, 100 - $row->reputation_score);
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

        $optionColumn = Schema::hasColumn('service_options', 'label')
            ? 'label'
            : (Schema::hasColumn('service_options', 'option_name') ? 'option_name' : null);

        if (!$optionColumn) {
            return DB::table('bookings as b_fallback')
                ->selectRaw('NULL as booking_id, NULL as areas_label')
                ->whereRaw('1 = 0');
        }

        return DB::table('booking_service_options as bso')
            ->join('service_options as so2', 'so2.id', '=', 'bso.service_option_id')
            ->selectRaw("bso.booking_id, GROUP_CONCAT(so2.$optionColumn ORDER BY so2.$optionColumn SEPARATOR ', ') as areas_label")
            ->groupBy('bso.booking_id');
    }

    private function providerNameExpression(string $alias): string
    {
        if (
            Schema::hasColumn('service_providers', 'first_name') &&
            Schema::hasColumn('service_providers', 'last_name')
        ) {
            return 'TRIM(' . $this->concatExpression([
                "COALESCE($alias.first_name,'')",
                "' '",
                "COALESCE($alias.last_name,'')",
            ]) . ')';
        }

        if (Schema::hasColumn('service_providers', 'name')) {
            return "COALESCE($alias.name, '')";
        }

        if (Schema::hasColumn('service_providers', 'full_name')) {
            return "COALESCE($alias.full_name, '')";
        }

        return $this->providerLabelExpression("$alias.id");
    }

    private function customerNameExpression(string $alias): string
    {
        if (Schema::hasColumn('customers', 'name')) {
            return "COALESCE(NULLIF(TRIM($alias.name), ''), 'Customer')";
        }

        if (Schema::hasColumn('customers', 'full_name')) {
            return "COALESCE(NULLIF(TRIM($alias.full_name), ''), 'Customer')";
        }

        return "'Customer'";
    }

    private function serviceNameExpression(string $alias): string
    {
        if (!Schema::hasTable('services')) {
            return "'Service'";
        }

        if (Schema::hasColumn('services', 'name')) {
            return "COALESCE($alias.name, 'Service')";
        }

        if (Schema::hasColumn('services', 'service_name')) {
            return "COALESCE($alias.service_name, 'Service')";
        }

        return "'Service'";
    }

    private function serviceOptionExpression(string $alias): string
    {
        if (!Schema::hasTable('service_options')) {
            return 'NULL';
        }

        if (Schema::hasColumn('service_options', 'label')) {
            return "$alias.label";
        }

        if (Schema::hasColumn('service_options', 'option_name')) {
            return "$alias.option_name";
        }

        return 'NULL';
    }

    private function providerLabelExpression(string $idExpression): string
    {
        return $this->concatExpression([
            "'Provider #'",
            $idExpression,
        ]);
    }

    private function concatExpression(array $parts): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return implode(' || ', $parts);
        }

        return 'CONCAT(' . implode(', ', $parts) . ')';
    }
}
