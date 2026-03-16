<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    // Keep route: /customer/dashboard -> dashboard()
    public function dashboard()
    {
        return $this->index();
    }

    public function index()
    {
        // Session-based guard (matches your middleware)
        if (!session()->has('user_id') || session('role') !== 'customer') {
            abort(403);
        }

        $customerId = (int) session('user_id');
        $name = session('name') ?? 'Customer';

        $tz = config('app.timezone') ?? 'Asia/Manila';

        // booking_date is DATE, so compare by date strings
        $todayDate = Carbon::now($tz)->toDateString();

        // Base bookings for this customer
        $base = Booking::query()->where('customer_id', $customerId);

        // In your DB: status contains paid/completed already (no payment_status column)
        $paidCompleted = Booking::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', ['paid', 'completed']);

        $stats = [
            'total_bookings'  => (clone $base)->count(),

            'active_bookings' => (clone $base)
                ->whereIn('status', ['confirmed', 'in_progress'])
                ->count(),

            'total_spent' => (clone $paidCompleted)->sum('price'),

            // "today" based on booking_date (DATE)
            'bookings_today'  => (clone $base)->whereDate('booking_date', $todayDate)->count(),
            'completed_today' => (clone $paidCompleted)->whereDate('booking_date', $todayDate)->count(),

            'spent_today'  => (clone $paidCompleted)->whereDate('booking_date', $todayDate)->sum('price'),
            'spent_month'  => (clone $paidCompleted)->whereMonth('booking_date', Carbon::now($tz)->month)
                                                   ->whereYear('booking_date', Carbon::now($tz)->year)
                                                   ->sum('price'),
            'spent_year'   => (clone $paidCompleted)->whereYear('booking_date', Carbon::now($tz)->year)->sum('price'),
        ];

        $recentCompleted = (clone $paidCompleted)
            ->orderByDesc('booking_date')
            ->orderByDesc('time_start')
            ->limit(5)
            ->get();

        return view('customer.dashboard', [
            'name' => $name,
            'stats' => $stats,
            'recentCompleted' => $recentCompleted,
        ]);
    }

    public function bookingsHistory(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'customer') {
            abort(403);
        }

        $customerId = (int) session('user_id');

        $q      = $request->q;
        $status = $request->status;
        $from   = $request->from;
        $to     = $request->to;
        $min    = $request->min;
        $max    = $request->max;

        $bookings = Booking::query()
            ->where('customer_id', $customerId)
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    // based on your schema: reference_code exists
                    $w->where('reference_code', 'like', "%{$q}%")
                      ->orWhere('contact_phone', 'like', "%{$q}%")
                      ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->when($status, fn ($qq) => $qq->where('status', $status))
            ->when($from, fn ($qq) => $qq->whereDate('booking_date', '>=', $from))
            ->when($to, fn ($qq) => $qq->whereDate('booking_date', '<=', $to))
            ->when($min !== null && $min !== '', fn ($qq) => $qq->where('price', '>=', $min))
            ->when($max !== null && $max !== '', fn ($qq) => $qq->where('price', '<=', $max))
            ->orderByDesc('booking_date')
            ->orderByDesc('time_start')
            ->paginate(10);

        return view('customer.bookings_history', compact('bookings'));
    }
}
