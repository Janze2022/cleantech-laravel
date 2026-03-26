<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function index(Request $request)
    {
        $tz = config('app.timezone') ?? 'Asia/Manila';
        $now = Carbon::now($tz);

        $start = $request->query('start', $now->copy()->subDays(29)->toDateString());
        $end   = $request->query('end', $now->toDateString());
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

        // Last 7 days status trend
        $last7Dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $last7Dates[] = $now->copy()->subDays($i)->toDateString();
        }

        $last7Labels = array_map(function ($d) use ($tz) {
            return Carbon::parse($d, $tz)->format('D');
        }, $last7Dates);

        $dailyMap = [];
        foreach ($last7Dates as $d) {
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

        foreach ($last7Dates as $d) {
            $dailyConfirmed[] = (int) $dailyMap[$d]['confirmed'];
            $dailyProgress[]  = (int) $dailyMap[$d]['in_progress'];
            $dailyPaid[]      = (int) $dailyMap[$d]['paid'];
            $dailyCompleted[] = (int) $dailyMap[$d]['completed'];
            $dailyCancelled[] = (int) $dailyMap[$d]['cancelled'];
        }

        // Monthly revenue for last 6 months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = $now->copy()->subMonths($i)->startOfMonth();
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
            'positives',
            'negatives'
        ));
    }

    public function export(Request $request)
    {
        $tz = config('app.timezone') ?? 'Asia/Manila';
        $now = Carbon::now($tz);

        $start = $request->query('start', $now->copy()->subDays(29)->toDateString());
        $end   = $request->query('end', $now->toDateString());

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
