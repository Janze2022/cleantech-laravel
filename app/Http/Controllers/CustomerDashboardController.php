<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

        $query = DB::table('bookings as b')
            ->where('b.customer_id', $customerId)
            ->select(
                'b.id',
                'b.reference_code',
                'b.booking_date',
                'b.time_start',
                'b.time_end',
                'b.price',
                'b.status',
                'b.address',
                'b.contact_phone'
            );

        if (Schema::hasColumn('bookings', 'created_at')) {
            $query->addSelect('b.created_at');
        }

        $bookings = $query
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('b.reference_code', 'like', "%{$q}%")
                        ->orWhere('b.contact_phone', 'like', "%{$q}%")
                        ->orWhere('b.address', 'like', "%{$q}%");
                });
            })
            ->when($status, fn ($qq) => $qq->where('b.status', $status))
            ->when($from, fn ($qq) => $qq->whereDate('b.booking_date', '>=', $from))
            ->when($to, fn ($qq) => $qq->whereDate('b.booking_date', '<=', $to))
            ->when($min !== null && $min !== '', fn ($qq) => $qq->where('b.price', '>=', $min))
            ->when($max !== null && $max !== '', fn ($qq) => $qq->where('b.price', '<=', $max))
            ->orderByDesc('b.booking_date')
            ->orderByDesc('b.time_start')
            ->paginate(10);

        return view('customer.bookings_history', compact('bookings'));
    }
}
