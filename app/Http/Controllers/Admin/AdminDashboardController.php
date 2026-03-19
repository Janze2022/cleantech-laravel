<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    private function normStatus($s): string
    {
        $s = strtolower(trim((string)$s));
        $s = str_replace(['-', ' '], '_', $s);

        // common variants
        if ($s === 'inprogress') $s = 'in_progress';
        if ($s === 'cancelled') $s = 'cancelled';
        if ($s === 'canceled')  $s = 'cancelled';
        if ($s === 'cancel')    $s = 'cancelled';

        return $s;
    }

    private function normalizedStatusSql(string $column = 'status'): string
    {
        return "LOWER(REPLACE(REPLACE({$column},'-','_'),' ','_'))";
    }

    private function dateOnlySql(string $column): string
    {
        return "DATE({$column})";
    }

    public function index(Request $request)
    {
        $tz  = config('app.timezone') ?? 'Asia/Manila';
        $now = Carbon::now($tz);

        $today      = $now->toDateString();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->endOfMonth()->toDateString();

        // =========================
        // CORE COUNTS (ALL TIME)
        // =========================
        $totalCustomers = (int) DB::table('customers')->count();
        $totalProviders = (int) DB::table('service_providers')->count();
        $totalBookings  = (int) DB::table('bookings')->count();

        // New today (created_at)
        $newCustomersToday = (int) DB::table('customers')->whereDate('created_at', $today)->count();
        $newProvidersToday = (int) DB::table('service_providers')->whereDate('created_at', $today)->count();

        $statusSql = $this->normalizedStatusSql('status');
        $createdDateExpr = $this->dateOnlySql('created_at');
        $updatedDateExpr = "DATE(COALESCE(updated_at, created_at))";

        $bookingsToday = (int) DB::table('bookings')
            ->whereRaw("$createdDateExpr = ?", [$today])
            ->count();

        // Keep this for compatibility with older widgets that still expect it.
        $confirmedToday = (int) DB::table('bookings')
            ->whereRaw("$statusSql = 'confirmed'")
            ->count();

        // Completed today is best approximated from the latest status update.
        $completedToday = (int) DB::table('bookings')
            ->whereRaw("$updatedDateExpr = ?", [$today])
            ->whereRaw("$statusSql = 'completed'")
            ->count();

        // Revenue uses the latest booking update rather than the scheduled booking date.
        $dailyIncome = (float) DB::table('bookings')
            ->whereRaw("$updatedDateExpr = ?", [$today])
            ->whereIn(DB::raw($statusSql), ['paid','completed'])
            ->sum(DB::raw('COALESCE(price,0)'));

        $monthlyRevenue = (float) DB::table('bookings')
            ->whereRaw("$updatedDateExpr BETWEEN ? AND ?", [$monthStart, $monthEnd])
            ->whereIn(DB::raw($statusSql), ['paid','completed'])
            ->sum(DB::raw('COALESCE(price,0)'));

        // =========================
        // STATUS COUNTS (ALL TIME)  ✅ (this is what you want for “actual”)
        // =========================
        $statusCounts = [
            'confirmed'   => 0,
            'in_progress' => 0,
            'paid'        => 0,
            'completed'   => 0,
            'cancelled'   => 0,
        ];

        $allStatusRows = DB::table('bookings')
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->get();

        foreach ($allStatusRows as $r) {
            $k = $this->normStatus($r->status ?? '');
            if (array_key_exists($k, $statusCounts)) {
                $statusCounts[$k] += (int) $r->cnt;
            }
        }

        // If you still need month status counts for other charts later, keep this:
        $statusCountsMonth = [
            'confirmed'   => 0,
            'in_progress' => 0,
            'paid'        => 0,
            'completed'   => 0,
            'cancelled'   => 0,
        ];

        $monthStatusRows = DB::table('bookings')
            ->whereRaw("$updatedDateExpr BETWEEN ? AND ?", [$monthStart, $monthEnd])
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->get();

        foreach ($monthStatusRows as $r) {
            $k = $this->normStatus($r->status ?? '');
            if (array_key_exists($k, $statusCountsMonth)) {
                $statusCountsMonth[$k] += (int) $r->cnt;
            }
        }

        // =========================
        // DAILY OPS (LAST 7 DAYS)
        // New bookings use created_at; completed uses the latest update date.
        // =========================
        $days7 = [];
        for ($i = 6; $i >= 0; $i--) $days7[] = $now->copy()->subDays($i)->toDateString();

        $dailyLabels = array_map(fn($d) => Carbon::parse($d, $tz)->format('D'), $days7);

        $dailyMap = [];
        foreach ($days7 as $d) $dailyMap[$d] = ['booked'=>0,'completed'=>0];

        $bookedRows = DB::table('bookings')
            ->whereRaw("$createdDateExpr BETWEEN ? AND ?", [$days7[0], $days7[6]])
            ->selectRaw("$createdDateExpr as d, COUNT(*) as cnt")
            ->groupBy('d')
            ->get();

        foreach ($bookedRows as $r) {
            $d = (string)($r->d ?? '');
            if (isset($dailyMap[$d])) {
                $dailyMap[$d]['booked'] = (int) $r->cnt;
            }
        }

        $completedRows = DB::table('bookings')
            ->whereRaw("$updatedDateExpr BETWEEN ? AND ?", [$days7[0], $days7[6]])
            ->whereRaw("$statusSql = 'completed'")
            ->selectRaw("$updatedDateExpr as d, COUNT(*) as cnt")
            ->groupBy('d')
            ->get();

        foreach ($completedRows as $r) {
            $d = (string)($r->d ?? '');
            if (isset($dailyMap[$d])) {
                $dailyMap[$d]['completed'] = (int) $r->cnt;
            }
        }

        $dailyBooked = [];
        $dailyCompleted = [];
        foreach ($days7 as $d) {
            $dailyBooked[] = (int)($dailyMap[$d]['booked'] ?? 0);
            $dailyCompleted[] = (int)($dailyMap[$d]['completed'] ?? 0);
        }

        // =========================
        // SYSTEM LOAD (based on last 7 days confirmed+completed)
        // =========================
        $ops7 = array_sum($dailyBooked) + array_sum($dailyCompleted);
        $cap = 70;
        $index = (int) max(0, min(100, round(($ops7 / max(1,$cap)) * 100)));

        // =========================
        // TREND REVENUE (LAST 30 DAYS)
        // =========================
        $trendStart = $now->copy()->subDays(29)->toDateString();
        $trendEnd   = $today;

        $trendMap = [];
        $trendRows = DB::table('bookings')
            ->whereRaw("$updatedDateExpr BETWEEN ? AND ?", [$trendStart, $trendEnd])
            ->whereIn(DB::raw($statusSql), ['paid','completed'])
            ->selectRaw("$updatedDateExpr as d, SUM(COALESCE(price,0)) as amt")
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        foreach ($trendRows as $r) {
            $trendMap[(string)$r->d] = (float)$r->amt;
        }

        $trendLabels = [];
        $trendRevenue = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $trendLabels[]  = Carbon::parse($d, $tz)->format('M d');
            $trendRevenue[] = round((float)($trendMap[$d] ?? 0), 2);
        }

        $stats = [
            'customers' => $totalCustomers,
            'providers' => $totalProviders,
            'bookings'  => $totalBookings,
        ];

        $chart = ['labels'=>[],'data'=>[],'income'=>[]];

        return view('admin.dashboard', compact(
            'stats','chart',
            'totalCustomers','totalProviders','totalBookings',
            'newCustomersToday','newProvidersToday','bookingsToday','confirmedToday','completedToday',
            'dailyIncome','monthlyRevenue',
            'statusCounts','statusCountsMonth',
            'dailyLabels','dailyBooked','dailyCompleted',
            'trendLabels','trendRevenue',
            'index'
        ));
    }
}
