<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    private function normalizeStatus($status): string
    {
        $status = strtolower(trim((string) $status));
        $status = str_replace(['-', ' '], '_', $status);

        if ($status === 'inprogress') {
            $status = 'in_progress';
        }

        if (in_array($status, ['canceled', 'cancel'], true)) {
            $status = 'cancelled';
        }

        return $status;
    }

    private function normalizedStatusSql(string $column = 'status'): string
    {
        $normalized = "LOWER(REPLACE(REPLACE(COALESCE({$column}, ''),'-','_'),' ','_'))";

        return "CASE
            WHEN {$normalized} IN ('cancelled', 'canceled', 'cancel') THEN 'cancelled'
            WHEN {$normalized} = 'inprogress' THEN 'in_progress'
            ELSE {$normalized}
        END";
    }

    private function bookingDateSql(string $column = 'b.booking_date'): string
    {
        return "DATE({$column})";
    }

    private function activityDateSql(
        string $updatedAtColumn = 'b.updated_at',
        string $createdAtColumn = 'b.created_at',
        string $bookingDateColumn = 'b.booking_date'
    ): string {
        return "DATE(COALESCE({$updatedAtColumn}, {$createdAtColumn}, {$bookingDateColumn}))";
    }

    private function reportDateSql(
        string $statusColumn = 'b.status',
        string $updatedAtColumn = 'b.updated_at',
        string $createdAtColumn = 'b.created_at',
        string $bookingDateColumn = 'b.booking_date'
    ): string {
        $statusSql = $this->normalizedStatusSql($statusColumn);
        $bookingDateSql = $this->bookingDateSql($bookingDateColumn);
        $activityDateSql = $this->activityDateSql($updatedAtColumn, $createdAtColumn, $bookingDateColumn);

        return "CASE
            WHEN {$statusSql} IN ('paid', 'completed', 'cancelled') THEN {$activityDateSql}
            ELSE COALESCE({$bookingDateSql}, {$activityDateSql})
        END";
    }

    private function applyReportRange($query, string $start, string $end)
    {
        $reportDateSql = $this->reportDateSql('b.status', 'b.updated_at', 'b.created_at', 'b.booking_date');

        return $query->whereRaw("{$reportDateSql} BETWEEN ? AND ?", [$start, $end]);
    }

    private function defaultReportStartDate(string $fallbackDate): string
    {
        $reportDateSql = $this->reportDateSql('b.status', 'b.updated_at', 'b.created_at', 'b.booking_date');

        return (string) (
            DB::table('bookings as b')
                ->selectRaw("MIN({$reportDateSql}) as first_report_date")
                ->value('first_report_date') ?: $fallbackDate
        );
    }

    private function customerReputationScore(object $row): int
    {
        $score = 100;

        if (($row->rating_count ?? 0) > 0) {
            $score -= max(0, (5 - (float) $row->avg_rating) * 12);
        }

        $score -= min(((int) $row->mismatch_count) * 12, 36);
        $score -= min(((int) $row->complaint_count) * 10, 40);
        $score -= min(((int) $row->cancelled_bookings) * 4, 24);
        $score += min(((int) $row->completed_bookings) * 2, 20);

        return (int) max(0, min(100, round($score)));
    }

    private function customerRiskLevel(object $row): string
    {
        if (
            (int) $row->complaint_count >= 3 ||
            (int) $row->mismatch_count >= 2 ||
            (((int) $row->rating_count) > 0 && (float) $row->avg_rating < 3.2) ||
            (int) $row->cancelled_bookings >= 4
        ) {
            return 'High';
        }

        if (
            (int) $row->complaint_count >= 1 ||
            (int) $row->mismatch_count >= 1 ||
            (((int) $row->rating_count) > 0 && (float) $row->avg_rating < 4.0) ||
            (int) $row->cancelled_bookings >= 2
        ) {
            return 'Medium';
        }

        return 'Low';
    }

    public function index(Request $request)
    {
        $tz = config('app.timezone') ?? 'Asia/Manila';
        $now = Carbon::now($tz);
        $fallbackEnd = $now->toDateString();
        $end = $request->query('end', $fallbackEnd);
        $start = $request->query('start', $this->defaultReportStartDate($fallbackEnd));
        $rangeEnd = Carbon::parse($end, $tz);
        $statusSql = $this->normalizedStatusSql('b.status');
        $reportDateSql = $this->reportDateSql('b.status', 'b.updated_at', 'b.created_at', 'b.booking_date');

        $base = $this->applyReportRange(DB::table('bookings as b'), $start, $end);

        // Totals
        $rangeBookings = (clone $base)->count();

        $rangeIncome = (float) (clone $base)
            ->whereIn(DB::raw($statusSql), ['paid', 'completed'])
            ->sum(DB::raw('COALESCE(b.price, 0)'));

        $cancelledLoss = (float) (clone $base)
            ->whereRaw("{$statusSql} = 'cancelled'")
            ->sum(DB::raw('COALESCE(b.price, 0)'));

        $netReport = $rangeIncome - $cancelledLoss;

        // Status counts
        $rows = (clone $base)
            ->selectRaw("{$statusSql} as status, COUNT(*) as cnt")
            ->groupBy(DB::raw($statusSql))
            ->get();

        $statusCounts = [
            'confirmed'   => 0,
            'in_progress' => 0,
            'paid'        => 0,
            'completed'   => 0,
            'cancelled'   => 0,
        ];

        foreach ($rows as $r) {
            $k = $this->normalizeStatus($r->status ?? '');
            if (array_key_exists($k, $statusCounts)) {
                $statusCounts[$k] += (int) $r->cnt;
            }
        }

        // Latest bookings list
        $bookings = (clone $base)
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->leftJoin('customers as c', 'c.id', '=', 'b.customer_id')
            ->leftJoin('service_providers as sp', 'sp.id', '=', 'b.provider_id')
            ->orderByDesc(DB::raw($reportDateSql))
            ->orderByDesc('b.updated_at')
            ->orderByDesc('b.created_at')
            ->select(
                'b.id',
                'b.reference_code',
                'b.booking_date',
                'b.time_start',
                'b.time_end',
                'b.status',
                'b.price',
                'b.created_at',
                'b.updated_at',
                'b.provider_id',
                'b.service_id',
                's.name as service_name',
                DB::raw("COALESCE(o.label,'') as option_label"),
                DB::raw("COALESCE(NULLIF(TRIM(c.name),''), NULLIF(TRIM(c.email),''), 'Customer') as customer_name"),
                DB::raw("TRIM(CONCAT(COALESCE(sp.first_name,''),' ',COALESCE(sp.last_name,''))) as provider_name"),
                DB::raw("{$reportDateSql} as report_date")
            )
            ->get()
            ->map(function ($booking) {
                $booking->status_key = $this->normalizeStatus($booking->status ?? '');
                return $booking;
            });

        // Status trend across the selected report range
        $rangeStart = Carbon::parse($start, $tz)->startOfDay();
        $rangeEndDay = Carbon::parse($end, $tz)->startOfDay();
        $rangeDates = [];

        for ($cursor = $rangeStart->copy(); $cursor->lte($rangeEndDay); $cursor->addDay()) {
            $rangeDates[] = $cursor->toDateString();
        }

        $last7Labels = array_map(function ($d) use ($tz, $rangeStart, $rangeEndDay) {
            $date = Carbon::parse($d, $tz);

            if ($rangeStart->equalTo($rangeEndDay)) {
                return $date->format('M d');
            }

            return $date->format('M d');
        }, $rangeDates);

        $dailyMap = [];
        foreach ($rangeDates as $d) {
            $dailyMap[$d] = [
                'confirmed'   => 0,
                'in_progress' => 0,
                'paid'        => 0,
                'completed'   => 0,
                'cancelled'   => 0,
            ];
        }

        foreach ($bookings as $b) {
            $d = substr((string)($b->report_date ?? $b->booking_date ?? ''), 0, 10);
            $st = $b->status_key ?? $this->normalizeStatus($b->status ?? '');
            if (isset($dailyMap[$d]) && isset($dailyMap[$d][$st])) {
                $dailyMap[$d][$st]++;
            }
        }

        $dailyConfirmed = [];
        $dailyProgress = [];
        $dailyPaid = [];
        $dailyCompleted = [];
        $dailyCancelled = [];

        foreach ($rangeDates as $d) {
            $dailyConfirmed[] = (int) $dailyMap[$d]['confirmed'];
            $dailyProgress[]  = (int) $dailyMap[$d]['in_progress'];
            $dailyPaid[]      = (int) $dailyMap[$d]['paid'];
            $dailyCompleted[] = (int) $dailyMap[$d]['completed'];
            $dailyCancelled[] = (int) $dailyMap[$d]['cancelled'];
        }

        // Monthly revenue for last 6 months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = $rangeEnd->copy()->subMonths($i)->startOfMonth();
        }

        $monthLabels = array_map(fn($m) => $m->format('M'), $months);
        $monthRevenue = array_fill(0, count($months), 0.0);

        foreach ($bookings as $b) {
            $st = $b->status_key ?? $this->normalizeStatus($b->status ?? '');
            if (!in_array($st, ['paid', 'completed'])) {
                continue;
            }

            try {
                $dt = Carbon::parse($b->report_date ?? $b->booking_date, $tz);
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($months as $idx => $m) {
                if ($dt->format('Y-m') === $m->format('Y-m')) {
                    $monthRevenue[$idx] += (float)($b->price ?? 0);
                    break;
                }
            }
        }

        $monthRevenue = array_map(fn($v) => round((float)$v, 2), $monthRevenue);

        // Provider performance
        $providerPerformance = (clone $base)
            ->leftJoin('service_providers as sp', 'sp.id', '=', 'b.provider_id')
            ->select(
                'b.provider_id',
                DB::raw("TRIM(CONCAT(COALESCE(sp.first_name,''),' ',COALESCE(sp.last_name,''))) as provider_name"),
                DB::raw("COUNT(*) as total_bookings"),
                DB::raw("SUM(CASE WHEN {$statusSql} IN ('paid','completed') THEN 1 ELSE 0 END) as success_count"),
                DB::raw("SUM(CASE WHEN {$statusSql} = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count"),
                DB::raw("SUM(CASE WHEN {$statusSql} IN ('paid','completed') THEN COALESCE(b.price, 0) ELSE 0 END) as revenue")
            )
            ->whereNotNull('b.provider_id')
            ->groupBy('b.provider_id', 'sp.first_name', 'sp.last_name')
            ->orderByDesc('revenue')
            ->orderByDesc('success_count')
            ->get()
            ->map(function ($p) {
                $total = (int) ($p->total_bookings ?? 0);
                $success = (int) ($p->success_count ?? 0);
                $cancelled = (int) ($p->cancelled_count ?? 0);

                $p->provider_name = trim((string)$p->provider_name) ?: 'Unnamed Provider';
                $p->completion_rate = $total > 0 ? round(($success / $total) * 100, 1) : 0;
                $p->cancel_rate = $total > 0 ? round(($cancelled / $total) * 100, 1) : 0;
                $p->revenue = (float) ($p->revenue ?? 0);
                return $p;
            });

        $topPerformer = $providerPerformance->first();

        // Provider chart data
        $topProviders = $providerPerformance->take(5);
        $providerLabels = $topProviders->pluck('provider_name')->values();
        $providerRevenue = $topProviders->pluck('revenue')->map(fn($v) => round((float)$v, 2))->values();

        // Service classification
        $serviceClassification = (clone $base)
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->select(
                DB::raw("COALESCE(NULLIF(TRIM(s.name),''), 'Uncategorized Service') as service_name"),
                DB::raw("COUNT(*) as total_bookings"),
                DB::raw("SUM(CASE WHEN {$statusSql} IN ('paid','completed') THEN COALESCE(b.price, 0) ELSE 0 END) as revenue"),
                DB::raw("SUM(CASE WHEN {$statusSql} = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count")
            )
            ->groupBy('s.name')
            ->orderByDesc('total_bookings')
            ->get()
            ->map(function ($s) {
                $s->revenue = (float) ($s->revenue ?? 0);
                return $s;
            });

        $serviceLabels = $serviceClassification->pluck('service_name')->values();
        $serviceBookings = $serviceClassification->pluck('total_bookings')->map(fn($v) => (int)$v)->values();

        $reportCustomerSummary = (object) [
            'customers' => 0,
            'rated_customers' => 0,
            'avg_rating' => 0,
            'high_risk' => 0,
            'mismatches' => 0,
            'complaints' => 0,
        ];
        $reportTopCustomers = collect();
        $reportProblematicCustomers = collect();

        if (Schema::hasTable('customers')) {
            $customerBookingStats = (clone $base)
                ->selectRaw("
                    b.customer_id,
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN {$statusSql} IN ('paid', 'completed') THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN {$statusSql} = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
                ")
                ->whereNotNull('b.customer_id')
                ->groupBy('b.customer_id');

            $customerRatingStats = null;
            if (Schema::hasTable('customer_ratings')) {
                $customerRatingStats = $this->applyReportRange(DB::table('bookings as b'), $start, $end)
                    ->join('customer_ratings as cr', 'cr.booking_id', '=', 'b.id')
                    ->selectRaw("
                        b.customer_id,
                        AVG(cr.rating) as avg_rating,
                        COUNT(cr.id) as rating_count,
                        SUM(CASE WHEN cr.flag_understated_area = 1 OR cr.flag_hidden_sections = 1 OR cr.flag_misleading_request = 1 THEN 1 ELSE 0 END) as rating_mismatch_count,
                        SUM(CASE WHEN cr.flag_difficult_behavior = 1 OR cr.flag_payment_issue = 1 OR cr.flag_last_minute_changes = 1 OR cr.unexpected_extra_work = 1 THEN 1 ELSE 0 END) as behavior_issue_count,
                        SUM(CASE WHEN cr.rating <= 2 THEN 1 ELSE 0 END) as low_rating_count
                    ")
                    ->whereNotNull('b.customer_id')
                    ->groupBy('b.customer_id');
            }

            $customerAdjustmentStats = null;
            if (Schema::hasTable('booking_adjustments')) {
                $customerAdjustmentStats = $this->applyReportRange(DB::table('bookings as b'), $start, $end)
                    ->join('booking_adjustments as ba', 'ba.booking_id', '=', 'b.id')
                    ->selectRaw('b.customer_id, COUNT(DISTINCT ba.booking_id) as adjustment_mismatch_count')
                    ->whereNotNull('b.customer_id')
                    ->groupBy('b.customer_id');
            }

            $customerQuery = DB::table('customers as c')
                ->joinSub($customerBookingStats, 'bs', function ($join) {
                    $join->on('bs.customer_id', '=', 'c.id');
                })
                ->select(
                    'c.id',
                    'c.name',
                    'c.email',
                    Schema::hasColumn('customers', 'phone')
                        ? 'c.phone'
                        : DB::raw('NULL as phone'),
                    DB::raw('COALESCE(bs.total_bookings, 0) as total_bookings'),
                    DB::raw('COALESCE(bs.completed_bookings, 0) as completed_bookings'),
                    DB::raw('COALESCE(bs.cancelled_bookings, 0) as cancelled_bookings')
                );

            if ($customerRatingStats) {
                $customerQuery->leftJoinSub($customerRatingStats, 'rs', function ($join) {
                    $join->on('rs.customer_id', '=', 'c.id');
                });

                $customerQuery->addSelect(
                    DB::raw('COALESCE(rs.avg_rating, 0) as avg_rating'),
                    DB::raw('COALESCE(rs.rating_count, 0) as rating_count'),
                    DB::raw('COALESCE(rs.rating_mismatch_count, 0) as rating_mismatch_count'),
                    DB::raw('COALESCE(rs.behavior_issue_count, 0) as behavior_issue_count'),
                    DB::raw('COALESCE(rs.low_rating_count, 0) as low_rating_count')
                );
            } else {
                $customerQuery->addSelect(
                    DB::raw('0 as avg_rating'),
                    DB::raw('0 as rating_count'),
                    DB::raw('0 as rating_mismatch_count'),
                    DB::raw('0 as behavior_issue_count'),
                    DB::raw('0 as low_rating_count')
                );
            }

            if ($customerAdjustmentStats) {
                $customerQuery->leftJoinSub($customerAdjustmentStats, 'adj', function ($join) {
                    $join->on('adj.customer_id', '=', 'c.id');
                });

                $customerQuery->addSelect(DB::raw('COALESCE(adj.adjustment_mismatch_count, 0) as adjustment_mismatch_count'));
            } else {
                $customerQuery->addSelect(DB::raw('0 as adjustment_mismatch_count'));
            }

            $reportCustomers = $customerQuery
                ->orderBy('c.name')
                ->get()
                ->map(function ($row) {
                    $row->avg_rating = round((float) ($row->avg_rating ?? 0), 1);
                    $row->total_bookings = (int) ($row->total_bookings ?? 0);
                    $row->completed_bookings = (int) ($row->completed_bookings ?? 0);
                    $row->cancelled_bookings = (int) ($row->cancelled_bookings ?? 0);
                    $row->rating_count = (int) ($row->rating_count ?? 0);
                    $row->rating_mismatch_count = (int) ($row->rating_mismatch_count ?? 0);
                    $row->adjustment_mismatch_count = (int) ($row->adjustment_mismatch_count ?? 0);
                    $row->behavior_issue_count = (int) ($row->behavior_issue_count ?? 0);
                    $row->low_rating_count = (int) ($row->low_rating_count ?? 0);
                    $row->mismatch_count = $row->rating_mismatch_count + $row->adjustment_mismatch_count;
                    $row->complaint_count = $row->behavior_issue_count + $row->low_rating_count;
                    $row->reputation_score = $this->customerReputationScore($row);
                    $row->risk_level = $this->customerRiskLevel($row);
                    $row->problem_index = ($row->mismatch_count * 3) + ($row->complaint_count * 4) + $row->cancelled_bookings;

                    return $row;
                })
                ->values();

            $reportCustomerSummary = (object) [
                'customers' => $reportCustomers->count(),
                'rated_customers' => $reportCustomers->where('rating_count', '>', 0)->count(),
                'avg_rating' => $reportCustomers->where('rating_count', '>', 0)->avg('avg_rating') ?? 0,
                'high_risk' => $reportCustomers->where('risk_level', 'High')->count(),
                'mismatches' => $reportCustomers->sum('mismatch_count'),
                'complaints' => $reportCustomers->sum('complaint_count'),
            ];

            $reportTopCustomers = $reportCustomers
                ->sortByDesc(function ($row) {
                    return ($row->reputation_score * 1000) + ($row->completed_bookings * 10) + $row->rating_count;
                })
                ->take(5)
                ->values();

            $reportProblematicCustomers = $reportCustomers
                ->sortByDesc(function ($row) {
                    return ($row->problem_index * 1000) + max(0, 100 - $row->reputation_score);
                })
                ->take(5)
                ->values();
        }

        // Positives and negatives
        $positives = collect();
        $negatives = collect();

        if ($rangeIncome > 0) {
            $positives->push('System generated income of ₱' . number_format($rangeIncome, 2) . ' within the selected range.');
        }

        if ($topPerformer) {
            $positives->push($topPerformer->provider_name . ' is the top performer with ₱' . number_format($topPerformer->revenue, 2) . ' revenue.');
        }

        if (($statusCounts['completed'] ?? 0) > ($statusCounts['cancelled'] ?? 0)) {
            $positives->push('Completed bookings are higher than cancelled bookings.');
        }

        if ($cancelledLoss > 0) {
            $negatives->push('Cancelled bookings caused an estimated loss of ₱' . number_format($cancelledLoss, 2) . '.');
        }

        if (($statusCounts['cancelled'] ?? 0) > 0) {
            $negatives->push('There are cancelled transactions that may affect customer trust and provider utilization.');
        }

        if ($providerPerformance->count() > 0) {
            $lowPerformer = $providerPerformance->sortBy('completion_rate')->first();
            if ($lowPerformer && $lowPerformer->total_bookings > 0) {
                $negatives->push($lowPerformer->provider_name . ' has the lowest completion rate at ' . number_format($lowPerformer->completion_rate, 1) . '%.');
            }
        }

        return view('admin.reports', compact(
            'start',
            'end',
            'rangeBookings',
            'rangeIncome',
            'cancelledLoss',
            'netReport',
            'statusCounts',
            'bookings',
            'last7Labels',
            'dailyConfirmed',
            'dailyProgress',
            'dailyPaid',
            'dailyCompleted',
            'dailyCancelled',
            'monthLabels',
            'monthRevenue',
            'providerPerformance',
            'topPerformer',
            'providerLabels',
            'providerRevenue',
            'serviceClassification',
            'serviceLabels',
            'serviceBookings',
            'reportCustomerSummary',
            'reportTopCustomers',
            'reportProblematicCustomers',
            'positives',
            'negatives'
        ));
    }

    public function export(Request $request)
    {
        $tz = config('app.timezone') ?? 'Asia/Manila';
        $now = Carbon::now($tz);
        $fallbackEnd = $now->toDateString();
        $end = $request->query('end', $fallbackEnd);
        $start = $request->query('start', $this->defaultReportStartDate($fallbackEnd));

        $rows = $this->applyReportRange(DB::table('bookings as b'), $start, $end)
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->leftJoin('service_providers as sp', 'sp.id', '=', 'b.provider_id')
            ->orderByDesc(DB::raw($this->reportDateSql('b.status', 'b.updated_at', 'b.created_at', 'b.booking_date')))
            ->select(
                'b.reference_code',
                'b.booking_date',
                'b.time_start',
                'b.time_end',
                'b.status',
                'b.price',
                's.name as service',
                DB::raw("COALESCE(o.label,'') as option_label"),
                DB::raw("TRIM(CONCAT(COALESCE(sp.first_name,''),' ',COALESCE(sp.last_name,''))) as provider_name"),
                DB::raw($this->reportDateSql('b.status', 'b.updated_at', 'b.created_at', 'b.booking_date') . " as report_date")
            )
            ->get();

        $filename = "reports_bookings_{$start}_to_{$end}.csv";

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Report Date', 'Booking Date', 'Time Start', 'Time End', 'Status', 'Price', 'Service', 'Option', 'Provider']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->reference_code,
                    $r->report_date,
                    $r->booking_date,
                    $r->time_start,
                    $r->time_end,
                    $r->status,
                    $r->price,
                    $r->service,
                    $r->option_label,
                    $r->provider_name,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
