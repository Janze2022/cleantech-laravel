<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderEarningsController extends Controller
{
    public function index()
    {
        $providerId = (int) session('provider_id');

        abort_if(!$providerId, 403, 'Provider session missing.');

        $earningStatuses = ['paid', 'completed'];

        $totalEarnings = (float) DB::table('bookings')
            ->where('provider_id', $providerId)
            ->whereIn('status', $earningStatuses)
            ->sum('price');

        $currentMonthEarnings = (float) DB::table('bookings')
            ->where('provider_id', $providerId)
            ->whereIn('status', $earningStatuses)
            ->whereYear('booking_date', now()->year)
            ->whereMonth('booking_date', now()->month)
            ->sum('price');

        $completedJobs = (int) DB::table('bookings')
            ->where('provider_id', $providerId)
            ->whereIn('status', $earningStatuses)
            ->count();

        $latestPaidBookings = DB::table('bookings as b')
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->where('b.provider_id', $providerId)
            ->whereIn('b.status', $earningStatuses)
            ->orderByDesc('b.booking_date')
            ->orderByDesc('b.updated_at')
            ->limit(8)
            ->select(
                'b.reference_code',
                'b.booking_date',
                'b.price',
                'b.status',
                's.name as service_name'
            )
            ->get();

        return view('provider.earnings', compact(
            'totalEarnings',
            'currentMonthEarnings',
            'completedJobs',
            'latestPaidBookings'
        ));
    }
}
