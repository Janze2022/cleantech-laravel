<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderDashboardController extends Controller
{
    public function index()
    {
        $providerId = session('provider_id');

        if (!$providerId) {
            return redirect()->route('provider.login');
        }

        $provider = DB::table('service_providers')
            ->where('id', $providerId)
            ->first();

        if (!$provider) {
            session()->forget('provider_id');
            return redirect()->route('provider.login');
        }

        $today = now()->toDateString();

        // Earnings recognized today based on status update time
        $todayEarnings = (float) DB::table('bookings')
            ->where('provider_id', $providerId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereDate('updated_at', $today)
            ->sum('price');

        // Total booking activity updated today
        $todayActivityTotal = (int) DB::table('bookings')
            ->where('provider_id', $providerId)
            ->whereDate('updated_at', $today)
            ->count();

        // Breakdown of today's updated statuses
        $todayStatusBreakdown = DB::table('bookings')
            ->where('provider_id', $providerId)
            ->whereDate('updated_at', $today)
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->get();

        // Bookings scheduled for today
        $todayScheduledTotal = (int) DB::table('bookings')
            ->where('provider_id', $providerId)
            ->whereDate('booking_date', $today)
            ->count();

        // Recent provider bookings
        $recentBookings = DB::table('bookings')
            ->where('provider_id', $providerId)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get([
                'reference_code',
                'booking_date',
                'requested_start_time',
                'time_start',
                'time_end',
                'status',
                'price',
                'created_at',
                'updated_at',
            ]);

        return view('provider.dashboard', compact(
            'provider',
            'todayEarnings',
            'todayActivityTotal',
            'todayStatusBreakdown',
            'todayScheduledTotal',
            'recentBookings'
        ));
    }

    public function pending()
    {
        return view('provider.pending');
    }
}