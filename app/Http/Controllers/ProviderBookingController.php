<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProviderBookingController extends Controller
{
    private function columnOrDefault(string $table, string $column, string $alias, ?string $as = null, string $default = 'NULL')
    {
        $as ??= $column;

        if (Schema::hasColumn($table, $column)) {
            return DB::raw("{$alias}.{$column} as {$as}");
        }

        return DB::raw("{$default} as {$as}");
    }

    private function providerId(): int
    {
        $providerId = (int) session('provider_id');

        if (!$providerId) {
            abort(403, 'Provider session missing.');
        }

        return $providerId;
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
            ->selectRaw("
                bso.booking_id,
                GROUP_CONCAT(so2.label ORDER BY so2.label SEPARATOR ', ') as areas_label
            ")
            ->groupBy('bso.booking_id');
    }

    private function customerNameSql(string $alias = 'c'): string
    {
        $parts = [];

        if (Schema::hasColumn('customers', 'name')) {
            $parts[] = "NULLIF(TRIM({$alias}.name), '')";
        }

        if (Schema::hasColumn('customers', 'first_name') && Schema::hasColumn('customers', 'last_name')) {
            $parts[] = "NULLIF(TRIM(CONCAT(IFNULL({$alias}.first_name, ''), ' ', IFNULL({$alias}.last_name, ''))), '')";
        } else {
            if (Schema::hasColumn('customers', 'first_name')) {
                $parts[] = "NULLIF(TRIM({$alias}.first_name), '')";
            }

            if (Schema::hasColumn('customers', 'last_name')) {
                $parts[] = "NULLIF(TRIM({$alias}.last_name), '')";
            }
        }

        if (Schema::hasColumn('customers', 'email')) {
            $parts[] = "NULLIF(TRIM({$alias}.email), '')";
        }

        if (empty($parts)) {
            return "'Customer'";
        }

        return 'COALESCE(' . implode(', ', $parts) . ", 'Customer')";
    }

    private function customerPhoneSql(string $customerAlias = 'c', string $bookingAlias = 'b'): string
    {
        $parts = [];

        if (Schema::hasColumn('customers', 'phone')) {
            $parts[] = "NULLIF(TRIM({$customerAlias}.phone), '')";
        }

        if (Schema::hasColumn('bookings', 'contact_phone')) {
            $parts[] = "NULLIF(TRIM({$bookingAlias}.contact_phone), '')";
        }

        if (empty($parts)) {
            return 'NULL';
        }

        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    private function customerEmailSql(string $alias = 'c'): string
    {
        return Schema::hasColumn('customers', 'email')
            ? "{$alias}.email"
            : 'NULL';
    }

    private function applyBookingSearch($query, string $q, bool $hasDirectOptionJoin): void
    {
        $query->where(function ($w) use ($q, $hasDirectOptionJoin) {
            $w->where('b.reference_code', 'like', "%{$q}%");

            if (Schema::hasColumn('services', 'name')) {
                $w->orWhere('s.name', 'like', "%{$q}%");
            }

            if ($hasDirectOptionJoin && Schema::hasColumn('service_options', 'label')) {
                $w->orWhere('o.label', 'like', "%{$q}%");
            }

            if (Schema::hasColumn('customers', 'name')) {
                $w->orWhere('c.name', 'like', "%{$q}%");
            }

            if (Schema::hasColumn('customers', 'first_name')) {
                $w->orWhere('c.first_name', 'like', "%{$q}%");
            }

            if (Schema::hasColumn('customers', 'last_name')) {
                $w->orWhere('c.last_name', 'like', "%{$q}%");
            }

            if (Schema::hasColumn('customers', 'email')) {
                $w->orWhere('c.email', 'like', "%{$q}%");
            }

            if (Schema::hasColumn('customers', 'phone')) {
                $w->orWhere('c.phone', 'like', "%{$q}%");
            }

            if (Schema::hasColumn('bookings', 'contact_phone')) {
                $w->orWhere('b.contact_phone', 'like', "%{$q}%");
            }

            if (Schema::hasColumn('bookings', 'address')) {
                $w->orWhere('b.address', 'like', "%{$q}%");
            }
        });
    }

    private function displayText($value, string $fallback = '-'): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : $fallback;
    }

    private function formatDateDisplay($value, string $format = 'M d, Y'): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return '-';
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    private function formatTimeDisplay($value, string $format = 'h:i A'): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return '-';
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    private function formatTimeRangeDisplay($start, $end): string
    {
        $startLabel = $this->formatTimeDisplay($start);
        $endLabel = $this->formatTimeDisplay($end);

        if ($startLabel === '-' && $endLabel === '-') {
            return '-';
        }

        if ($startLabel === '-') {
            return $endLabel;
        }

        if ($endLabel === '-') {
            return $startLabel;
        }

        return $startLabel . ' - ' . $endLabel;
    }

    private function decorateBookings($bookings)
    {
        return $bookings->map(function ($booking) {
            $booking->display_booking_date = $this->formatDateDisplay($booking->booking_date);
            $booking->display_requested_start_time = $this->formatTimeDisplay($booking->requested_start_time);
            $booking->display_availability = $this->formatTimeRangeDisplay($booking->time_start, $booking->time_end);
            $booking->display_time_range = $this->formatTimeRangeDisplay($booking->time_start, $booking->time_end);
            $booking->display_option = $this->displayText($booking->option);
            $booking->display_email = $this->displayText($booking->email);
            $booking->display_phone = $this->displayText($booking->contact_phone ?? $booking->phone);
            $booking->display_price = number_format((float) ($booking->price ?? 0), 2);

            return $booking;
        });
    }

    /**
     * ACTIVE BOOKINGS (provider can still update status)
     * Status: confirmed, in_progress, paid
     */
    public function index()
    {
        $providerId = $this->providerId();
        $areasSub = $this->bookingAreasSubquery();
        $hasDirectOptionJoin = Schema::hasTable('service_options')
            && Schema::hasColumn('service_options', 'id')
            && Schema::hasColumn('bookings', 'service_option_id');
        $optionSql = $hasDirectOptionJoin && Schema::hasColumn('service_options', 'label')
            ? 'o.label'
            : 'NULL';

        $bookings = DB::table('bookings as b')
            ->leftJoin('customers as c', 'c.id', '=', 'b.customer_id')
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->where('b.provider_id', $providerId)
            ->whereIn('b.status', ['confirmed', 'in_progress', 'paid'])
            ->orderByDesc('b.created_at')
            ->select(
                'b.reference_code',
                $this->columnOrDefault('bookings', 'booking_date', 'b'),
                $this->columnOrDefault('bookings', 'requested_start_time', 'b'),
                $this->columnOrDefault('bookings', 'time_start', 'b'),
                $this->columnOrDefault('bookings', 'time_end', 'b'),
                'b.status',
                $this->columnOrDefault('bookings', 'price', 'b', null, '0'),
                $this->columnOrDefault('bookings', 'address', 'b'),
                $this->columnOrDefault('bookings', 'contact_phone', 'b'),
                DB::raw($this->customerNameSql('c') . ' as name'),
                DB::raw($this->customerPhoneSql('c', 'b') . ' as phone'),
                DB::raw($this->customerEmailSql('c') . ' as email'),
                DB::raw("COALESCE(s.name, 'Service') as service"),
                DB::raw("COALESCE(areas.areas_label, {$optionSql}) as option"),
                $this->columnOrDefault('bookings', 'created_at', 'b')
            );

        if ($hasDirectOptionJoin) {
            $bookings->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id');
        }

        $bookings = $bookings->get();

        $bookings = $this->decorateBookings($bookings);

        return view('provider.bookings', compact('bookings'));
    }

    /**
     * PAST BOOKINGS (history)
     * Status: paid, completed, cancelled
     */
    public function history(Request $request)
    {
        $providerId = $this->providerId();
        $areasSub = $this->bookingAreasSubquery();
        $hasDirectOptionJoin = Schema::hasTable('service_options')
            && Schema::hasColumn('service_options', 'id')
            && Schema::hasColumn('bookings', 'service_option_id');
        $optionSql = $hasDirectOptionJoin && Schema::hasColumn('service_options', 'label')
            ? 'o.label'
            : 'NULL';

        $q      = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');
        $from   = (string) $request->query('from', '');
        $to     = (string) $request->query('to', '');

        $query = DB::table('bookings as b')
            ->leftJoin('customers as c', 'c.id', '=', 'b.customer_id')
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->where('b.provider_id', $providerId)
            ->whereIn('b.status', ['paid', 'completed', 'cancelled'])
            ->select(
                'b.reference_code',
                $this->columnOrDefault('bookings', 'booking_date', 'b'),
                $this->columnOrDefault('bookings', 'requested_start_time', 'b'),
                $this->columnOrDefault('bookings', 'time_start', 'b'),
                $this->columnOrDefault('bookings', 'time_end', 'b'),
                'b.status',
                $this->columnOrDefault('bookings', 'price', 'b', null, '0'),
                $this->columnOrDefault('bookings', 'address', 'b'),
                $this->columnOrDefault('bookings', 'contact_phone', 'b'),
                DB::raw($this->customerNameSql('c') . ' as name'),
                DB::raw($this->customerPhoneSql('c', 'b') . ' as phone'),
                DB::raw($this->customerEmailSql('c') . ' as email'),
                DB::raw("COALESCE(s.name, 'Service') as service"),
                DB::raw("COALESCE(areas.areas_label, {$optionSql}) as option"),
                $this->columnOrDefault('bookings', 'created_at', 'b')
            );

        if ($hasDirectOptionJoin) {
            $query->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id');
        }

        if ($from) {
            $query->whereDate('b.booking_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('b.booking_date', '<=', $to);
        }

        if ($status === 'not_completed') {
            $query->whereIn('b.status', ['paid', 'cancelled']);
        } elseif ($status !== 'all' && in_array($status, ['paid', 'completed', 'cancelled'], true)) {
            $query->where('b.status', $status);
        }

        if ($q !== '') {
            $this->applyBookingSearch($query, $q, $hasDirectOptionJoin);
        }

        $bookings = $query
            ->orderByDesc('b.booking_date')
            ->orderByDesc('b.created_at')
            ->get();

        $bookings = $this->decorateBookings($bookings);

        return view('provider.bookings_history', compact('bookings', 'q', 'status', 'from', 'to'));
    }

    public function show(string $reference_code)
    {
        $providerId = $this->providerId();
        $areasSub = $this->bookingAreasSubquery();
        $hasDirectOptionJoin = Schema::hasTable('service_options')
            && Schema::hasColumn('service_options', 'id')
            && Schema::hasColumn('bookings', 'service_option_id');
        $optionSql = $hasDirectOptionJoin && Schema::hasColumn('service_options', 'label')
            ? 'o.label'
            : "''";

        $booking = DB::table('bookings as b')
            ->leftJoin('customers as c', 'c.id', '=', 'b.customer_id')
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->where('b.provider_id', $providerId)
            ->where('b.reference_code', $reference_code)
            ->select(
                'b.*',
                DB::raw($this->customerNameSql('c') . ' as customer_name'),
                DB::raw($this->customerEmailSql('c') . ' as customer_email'),
                DB::raw($this->customerPhoneSql('c', 'b') . ' as customer_phone'),
                DB::raw("COALESCE(s.name, 'Service') as service_name"),
                DB::raw("COALESCE(areas.areas_label, {$optionSql}, '') as option_label")
            );

        if ($hasDirectOptionJoin) {
            $booking->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id');
        }

        $booking = $booking->first();

        abort_if(!$booking, 404);

        return view('provider.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, string $reference)
    {
        $providerId = $this->providerId();

        $data = $request->validate([
            'status' => 'required|string|in:confirmed,in_progress,paid,completed,cancelled',
        ]);

        $booking = DB::table('bookings')
            ->where('provider_id', $providerId)
            ->where('reference_code', $reference)
            ->select('id', 'status', 'customer_id', 'reference_code')
            ->first();

        abort_if(!$booking, 404);

        $allowed = [
            'confirmed'   => ['in_progress', 'cancelled'],
            'in_progress' => ['paid', 'cancelled'],
            'paid'        => ['completed'],
            'completed'   => [],
            'cancelled'   => [],
        ];

        $current = strtolower((string) $booking->status);
        $next    = strtolower((string) $data['status']);

        if ($next === $current) {
            return back()->with('success', 'Status unchanged.');
        }

        if (!in_array($next, $allowed[$current] ?? [], true)) {
            return back()->withErrors([
                'status' => "Invalid status change: {$current} → {$next}",
            ]);
        }

        DB::transaction(function () use ($booking, $next) {
            $update = ['status' => $next];

            if (Schema::hasColumn('bookings', 'updated_at')) {
                $update['updated_at'] = now();
            }

            DB::table('bookings')
                ->where('id', $booking->id)
                ->update($update);

            $statusLabel = match ($next) {
                'confirmed'   => 'Confirmed',
                'in_progress' => 'In Progress',
                'paid'        => 'Paid',
                'completed'   => 'Completed',
                'cancelled'   => 'Cancelled',
                default       => ucfirst(str_replace('_', ' ', $next)),
            };

            $message = match ($next) {
                'in_progress' => 'Your booking is now in progress.',
                'paid'        => 'Your booking has been marked as paid.',
                'completed'   => 'Your service has been completed. Please leave a review.',
                'cancelled'   => 'Your booking has been marked as cancelled by the provider.',
                'confirmed'   => 'Your booking has been confirmed.',
                default       => 'Your booking status has been updated to ' . $statusLabel . '.',
            };

            $type = Schema::hasColumn('notifications', 'type')
                ? ($next === 'completed' ? 'review' : 'booking_status')
                : null;

            $hasUpdatedAt = Schema::hasColumn('notifications', 'updated_at');
            $hasCreatedAt = Schema::hasColumn('notifications', 'created_at');

            $uniqueKeys = [
                'user_id'        => $booking->customer_id,
                'reference_code' => $booking->reference_code,
            ];

            if ($type !== null) {
                $uniqueKeys['type'] = $type;
            }

            $values = [
                'message' => $message,
                'is_read' => 0,
            ];

            if ($type !== null) {
                $values['type'] = $type;
            }

            if ($hasCreatedAt) {
                $values['created_at'] = now();
            }

            if ($hasUpdatedAt) {
                $values['updated_at'] = now();
            }

            DB::table('notifications')->updateOrInsert($uniqueKeys, $values);
        });

        return back()->with('success', 'Booking status updated to ' . strtoupper($next) . '.');
    }

    public function analytics(Request $request)
    {
        $providerId = $this->providerId();

        $days = (int) $request->query('days', 14);
        if ($days < 7) $days = 7;
        if ($days > 60) $days = 60;

        $months = (int) $request->query('months', 12);
        if ($months < 3) $months = 3;
        if ($months > 24) $months = 24;

        $selectedDate = trim((string) $request->query('date', ''));
        $fromDate     = trim((string) $request->query('from_date', ''));
        $toDate       = trim((string) $request->query('to_date', ''));

        $earningStatuses = ['paid', 'completed'];

        $earningsBase = DB::table('bookings as b')
            ->where('b.provider_id', $providerId)
            ->whereIn('b.status', $earningStatuses);

        if ($selectedDate !== '') {
            $earningsBase->whereDate('b.booking_date', $selectedDate);
        } else {
            if ($fromDate !== '') {
                $earningsBase->whereDate('b.booking_date', '>=', $fromDate);
            }

            if ($toDate !== '') {
                $earningsBase->whereDate('b.booking_date', '<=', $toDate);
            }
        }

        $daily = (clone $earningsBase)
            ->whereDate('b.booking_date', '>=', now()->subDays($days - 1)->toDateString())
            ->groupBy('b.booking_date')
            ->orderBy('b.booking_date', 'asc')
            ->selectRaw('b.booking_date as label, SUM(b.price) as amount')
            ->get();

        $monthly = (clone $earningsBase)
            ->whereDate('b.booking_date', '>=', now()->subMonths($months - 1)->startOfMonth()->toDateString())
            ->selectRaw("
                DATE_FORMAT(b.booking_date, '%Y-%m-01') as month_key,
                DATE_FORMAT(MIN(b.booking_date), '%b %Y') as label,
                SUM(b.price) as amount
            ")
            ->groupBy('month_key')
            ->orderBy('month_key', 'asc')
            ->get();

        $annualTotal = (float) (
            DB::table('bookings')
                ->where('provider_id', $providerId)
                ->whereIn('status', $earningStatuses)
                ->whereYear('booking_date', now()->year)
                ->sum('price') ?? 0
        );

        $statusBreakdown = DB::table('bookings')
            ->where('provider_id', $providerId)
            ->when($selectedDate !== '', function ($q) use ($selectedDate) {
                $q->whereDate('booking_date', $selectedDate);
            })
            ->when($selectedDate === '' && $fromDate !== '', function ($q) use ($fromDate) {
                $q->whereDate('booking_date', '>=', $fromDate);
            })
            ->when($selectedDate === '' && $toDate !== '', function ($q) use ($toDate) {
                $q->whereDate('booking_date', '<=', $toDate);
            })
            ->when($selectedDate === '' && $fromDate === '' && $toDate === '', function ($q) {
                $q->whereDate('booking_date', '>=', now()->subMonths(6)->toDateString());
            })
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as cnt')
            ->get();

        $dateEarnings = (clone $earningsBase)
            ->groupBy('b.booking_date')
            ->orderBy('b.booking_date', 'desc')
            ->selectRaw('b.booking_date as date, SUM(b.price) as amount, COUNT(*) as bookings_count')
            ->get();

        $topDate = (clone $earningsBase)
            ->groupBy('b.booking_date')
            ->orderByRaw('SUM(b.price) DESC')
            ->selectRaw('b.booking_date as date, SUM(b.price) as amount, COUNT(*) as bookings_count')
            ->first();

        $topServices = DB::table('bookings as b')
            ->join('services as s', 's.id', '=', 'b.service_id')
            ->where('b.provider_id', $providerId)
            ->when($selectedDate !== '', function ($q) use ($selectedDate) {
                $q->whereDate('b.booking_date', $selectedDate);
            })
            ->when($selectedDate === '' && $fromDate !== '', function ($q) use ($fromDate) {
                $q->whereDate('b.booking_date', '>=', $fromDate);
            })
            ->when($selectedDate === '' && $toDate !== '', function ($q) use ($toDate) {
                $q->whereDate('b.booking_date', '<=', $toDate);
            })
            ->groupBy('s.id', 's.name')
            ->orderByDesc(DB::raw('COUNT(b.id)'))
            ->selectRaw("
                s.name as service_name,
                COUNT(b.id) as bookings_count,
                SUM(CASE WHEN b.status IN ('paid','completed') THEN b.price ELSE 0 END) as earnings
            ")
            ->limit(8)
            ->get();

        return view('provider.analytics', compact(
            'daily',
            'monthly',
            'annualTotal',
            'statusBreakdown',
            'days',
            'months',
            'selectedDate',
            'fromDate',
            'toDate',
            'dateEarnings',
            'topDate',
            'topServices'
        ));
    }
}
