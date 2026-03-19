<?php

namespace App\Http\Controllers;

use App\Services\GeoapifyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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

    private function tableHasColumns(string $table, array $columns): bool
    {
        return Schema::hasTable($table) && Schema::hasColumns($table, $columns);
    }

    private function providerLocationTableAvailable(): bool
    {
        return $this->tableHasColumns('booking_provider_locations', [
            'booking_id',
            'provider_id',
            'latitude',
            'longitude',
            'formatted_address',
            'is_tracking',
            'tracked_at',
            'stopped_at',
        ]);
    }

    private function filledString($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    private function firstFilled(array $values, ?string $fallback = null): ?string
    {
        foreach ($values as $value) {
            $value = $this->filledString($value);

            if ($value !== null) {
                return $value;
            }
        }

        return $fallback;
    }

    private function normalizeStatusKey($value): string
    {
        $value = strtolower(trim((string) ($value ?? '')));

        if ($value === '') {
            return '';
        }

        $value = str_replace(['-', ' '], '_', $value);

        if ($value === 'inprogress') {
            $value = 'in_progress';
        }

        if (in_array($value, ['canceled', 'cancel'], true)) {
            $value = 'cancelled';
        }

        return $value;
    }

    private function activeStatusKeys(): array
    {
        return ['pending', 'confirmed', 'in_progress', 'paid'];
    }

    private function historyStatusKeys(): array
    {
        return ['completed', 'cancelled'];
    }

    private function trackingStatusKeys(): array
    {
        return ['confirmed', 'in_progress', 'paid'];
    }

    private function bookingAreasSubquery()
    {
        if (
            !Schema::hasTable('booking_service_options') ||
            !Schema::hasTable('service_options') ||
            !Schema::hasColumns('booking_service_options', ['booking_id', 'service_option_id']) ||
            !Schema::hasColumns('service_options', ['id', 'label'])
        ) {
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

    private function bookingsTableAvailable(): bool
    {
        return Schema::hasTable('bookings')
            && Schema::hasColumns('bookings', ['provider_id', 'status', 'reference_code']);
    }

    private function customersJoinAvailable(): bool
    {
        return Schema::hasTable('customers')
            && Schema::hasColumn('customers', 'id')
            && Schema::hasColumn('bookings', 'customer_id');
    }

    private function servicesJoinAvailable(): bool
    {
        return Schema::hasTable('services')
            && Schema::hasColumn('services', 'id')
            && Schema::hasColumn('bookings', 'service_id');
    }

    private function directOptionJoinAvailable(): bool
    {
        return Schema::hasTable('service_options')
            && Schema::hasColumns('service_options', ['id', 'label'])
            && Schema::hasColumn('bookings', 'service_option_id');
    }

    private function customerNameFromRecord($customer): string
    {
        if (!$customer) {
            return 'Customer';
        }

        return $this->firstFilled([
            $customer->name ?? null,
            trim(implode(' ', array_filter([
                $customer->first_name ?? null,
                $customer->last_name ?? null,
            ]))),
            $customer->first_name ?? null,
            $customer->last_name ?? null,
            $customer->email ?? null,
        ], 'Customer');
    }

    private function customerPhoneFromRecord($customer, $booking): ?string
    {
        return $this->firstFilled([
            $customer->phone ?? null,
            $booking->contact_phone ?? null,
        ]);
    }

    private function customerEmailFromRecord($customer): ?string
    {
        return $this->filledString($customer->email ?? null);
    }

    private function serviceNameFromRecord($service): string
    {
        return $this->firstFilled([
            $service->name ?? null,
        ], 'Service');
    }

    private function applyBookingsOrder($query, array $preferredColumns): void
    {
        foreach ($preferredColumns as $column) {
            if (Schema::hasColumn('bookings', $column)) {
                $query->orderByDesc('b.' . $column);
                return;
            }
        }

        $query->orderByDesc('b.reference_code');
    }

    private function selectBaseBookings(int $providerId)
    {
        $query = DB::table('bookings as b')
            ->where('b.provider_id', $providerId)
            ->select(
                'b.reference_code',
                $this->columnOrDefault('bookings', 'id', 'b'),
                $this->columnOrDefault('bookings', 'customer_id', 'b'),
                $this->columnOrDefault('bookings', 'service_id', 'b'),
                $this->columnOrDefault('bookings', 'service_option_id', 'b'),
                $this->columnOrDefault('bookings', 'booking_date', 'b'),
                $this->columnOrDefault('bookings', 'requested_start_time', 'b'),
                $this->columnOrDefault('bookings', 'time_start', 'b'),
                $this->columnOrDefault('bookings', 'time_end', 'b'),
                'b.status',
                $this->columnOrDefault('bookings', 'price', 'b', null, '0'),
                $this->columnOrDefault('bookings', 'address', 'b'),
                $this->columnOrDefault('bookings', 'contact_phone', 'b'),
                $this->columnOrDefault('bookings', 'created_at', 'b'),
                $this->columnOrDefault('bookings', 'updated_at', 'b')
            );

        $this->applyBookingsOrder($query, ['created_at', 'booking_date', 'id']);

        return $query->get();
    }

    private function selectBookingLocationColumn(string $column)
    {
        return Schema::hasColumn('bookings', $column)
            ? DB::raw("b.{$column} as {$column}")
            : DB::raw("NULL as {$column}");
    }

    private function bookingByReference(string $reference, int $providerId)
    {
        return DB::table('bookings')
            ->where('provider_id', $providerId)
            ->where('reference_code', $reference)
            ->first([
                'id',
                'provider_id',
                'reference_code',
                'status',
                Schema::hasColumn('bookings', 'customer_latitude')
                    ? 'customer_latitude'
                    : DB::raw('NULL as customer_latitude'),
                Schema::hasColumn('bookings', 'customer_longitude')
                    ? 'customer_longitude'
                    : DB::raw('NULL as customer_longitude'),
                Schema::hasColumn('bookings', 'formatted_address')
                    ? 'formatted_address'
                    : DB::raw('NULL as formatted_address'),
                'address',
            ]);
    }

    private function latestProviderLocation(int $bookingId): ?object
    {
        if (!$this->providerLocationTableAvailable()) {
            return null;
        }

        return DB::table('booking_provider_locations')
            ->where('booking_id', $bookingId)
            ->select(
                'latitude',
                'longitude',
                'formatted_address',
                'is_tracking',
                'tracked_at',
                'updated_at',
                'stopped_at'
            )
            ->first();
    }

    private function bookingCanTrack(?string $status): bool
    {
        return in_array($this->normalizeStatusKey($status), $this->trackingStatusKeys(), true);
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

    private function bookingAreasMap($bookingIds)
    {
        $bookingIds = collect($bookingIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->unique()
            ->values();

        if (
            $bookingIds->isEmpty() ||
            !$this->tableHasColumns('booking_service_options', ['booking_id', 'service_option_id']) ||
            !$this->tableHasColumns('service_options', ['id', 'label'])
        ) {
            return collect();
        }

        return DB::table('booking_service_options as bso')
            ->join('service_options as so', 'so.id', '=', 'bso.service_option_id')
            ->whereIn('bso.booking_id', $bookingIds)
            ->orderBy('so.label')
            ->get(['bso.booking_id', 'so.label'])
            ->groupBy('booking_id')
            ->map(function ($rows) {
                return $rows->pluck('label')
                    ->map(fn ($label) => trim((string) $label))
                    ->filter()
                    ->unique()
                    ->implode(', ');
            });
    }

    private function enrichBookings($bookings)
    {
        $bookings = collect($bookings)->values();

        if ($bookings->isEmpty()) {
            return $bookings;
        }

        $customers = collect();
        if ($this->customersJoinAvailable()) {
            $customerIds = $bookings->pluck('customer_id')
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->unique()
                ->values();

            if ($customerIds->isNotEmpty()) {
                $columns = ['id'];

                foreach (['name', 'first_name', 'last_name', 'email', 'phone'] as $column) {
                    if (Schema::hasColumn('customers', $column)) {
                        $columns[] = $column;
                    }
                }

                $customers = DB::table('customers')
                    ->whereIn('id', $customerIds)
                    ->get($columns)
                    ->keyBy('id');
            }
        }

        $services = collect();
        if ($this->servicesJoinAvailable()) {
            $serviceIds = $bookings->pluck('service_id')
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->unique()
                ->values();

            if ($serviceIds->isNotEmpty()) {
                $columns = ['id'];

                if (Schema::hasColumn('services', 'name')) {
                    $columns[] = 'name';
                }

                $services = DB::table('services')
                    ->whereIn('id', $serviceIds)
                    ->get($columns)
                    ->keyBy('id');
            }
        }

        $options = collect();
        if ($this->directOptionJoinAvailable()) {
            $optionIds = $bookings->pluck('service_option_id')
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->unique()
                ->values();

            if ($optionIds->isNotEmpty()) {
                $options = DB::table('service_options')
                    ->whereIn('id', $optionIds)
                    ->get(['id', 'label'])
                    ->keyBy('id');
            }
        }

        $areasMap = $this->bookingAreasMap($bookings->pluck('id'));

        return $bookings->map(function ($booking) use ($customers, $services, $options, $areasMap) {
            $customer = $customers->get($booking->customer_id);
            $service = $services->get($booking->service_id);
            $option = $options->get($booking->service_option_id);
            $statusKey = $this->normalizeStatusKey($booking->status);

            $booking->raw_status = $booking->status;
            $booking->status_key = $statusKey !== '' ? $statusKey : 'unknown';
            $booking->status = $booking->status_key;
            $booking->name = $this->customerNameFromRecord($customer);
            $booking->phone = $this->customerPhoneFromRecord($customer, $booking);
            $booking->email = $this->customerEmailFromRecord($customer);
            $booking->service = $this->serviceNameFromRecord($service);
            $booking->option = $this->firstFilled([
                $areasMap->get($booking->id),
                $option->label ?? null,
            ]);
            $booking->display_booking_date = $this->formatDateDisplay($booking->booking_date);
            $booking->display_requested_start_time = $this->formatTimeDisplay($booking->requested_start_time);
            $booking->display_availability = $this->formatTimeRangeDisplay($booking->time_start, $booking->time_end);
            $booking->display_time_range = $this->formatTimeRangeDisplay($booking->time_start, $booking->time_end);
            $booking->display_option = $this->displayText($booking->option);
            $booking->display_email = $this->displayText($booking->email);
            $booking->display_phone = $this->displayText($booking->phone ?? $booking->contact_phone);
            $booking->display_price = number_format((float) ($booking->price ?? 0), 2);

            return $booking;
        })->values();
    }

    private function filterBookingsByStatuses($bookings, array $allowedStatuses)
    {
        $allowedStatuses = array_values(array_unique(array_map(
            fn ($status) => $this->normalizeStatusKey($status),
            $allowedStatuses
        )));

        return collect($bookings)
            ->filter(function ($booking) use ($allowedStatuses) {
                return in_array($booking->status_key ?? $this->normalizeStatusKey($booking->status), $allowedStatuses, true);
            })
            ->values();
    }

    private function matchesBookingSearch($booking, string $q): bool
    {
        if ($q === '') {
            return true;
        }

        $haystack = strtolower(implode(' ', array_filter([
            $booking->reference_code ?? null,
            $booking->name ?? null,
            $booking->email ?? null,
            $booking->display_phone ?? $booking->phone ?? $booking->contact_phone ?? null,
            $booking->service ?? null,
            $booking->option ?? null,
            $booking->address ?? null,
        ])));

        return str_contains($haystack, strtolower($q));
    }

    private function comparableDate($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function bookingWithinRange($booking, string $from, string $to): bool
    {
        $bookingDate = $this->comparableDate($booking->booking_date ?? null);

        if ($bookingDate === null) {
            return $from === '' && $to === '';
        }

        $fromDate = $this->comparableDate($from);
        $toDate = $this->comparableDate($to);

        if ($fromDate !== null && $bookingDate < $fromDate) {
            return false;
        }

        if ($toDate !== null && $bookingDate > $toDate) {
            return false;
        }

        return true;
    }

    /**
     * ACTIVE BOOKINGS (provider can still update status)
     * Status: pending, confirmed, in_progress, paid
     */
    public function index()
    {
        $providerId = $this->providerId();
        $bookings = collect();
        $loadError = null;

        if (!$this->bookingsTableAvailable()) {
            $loadError = 'Bookings data is not available right now.';
            return view('provider.bookings', compact('bookings', 'loadError'));
        }

        try {
            $bookings = $this->filterBookingsByStatuses(
                $this->enrichBookings($this->selectBaseBookings($providerId)),
                $this->activeStatusKeys()
            );
        } catch (\Throwable $e) {
            report($e);
            $loadError = 'Unable to load bookings right now.';
        }

        return view('provider.bookings', compact('bookings', 'loadError'));
    }

    /**
     * PAST BOOKINGS (history)
     * Status: paid, completed, cancelled
     */
    public function history(Request $request)
    {
        $providerId = $this->providerId();
        $q      = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');
        $from   = (string) $request->query('from', '');
        $to     = (string) $request->query('to', '');
        $bookings = collect();
        $loadError = null;

        if (!$this->bookingsTableAvailable()) {
            $loadError = 'Booking history is not available right now.';
            return view('provider.bookings_history', compact('bookings', 'q', 'status', 'from', 'to', 'loadError'));
        }

        try {
            $bookings = $this->filterBookingsByStatuses(
                $this->enrichBookings($this->selectBaseBookings($providerId)),
                ['paid', 'completed', 'cancelled']
            )->filter(function ($booking) use ($q, $status, $from, $to) {
                if (!$this->bookingWithinRange($booking, $from, $to)) {
                    return false;
                }

                $statusKey = $booking->status_key ?? $this->normalizeStatusKey($booking->status);

                if ($status === 'not_completed' && !in_array($statusKey, ['paid', 'cancelled'], true)) {
                    return false;
                }

                if (
                    $status !== 'all' &&
                    $status !== 'not_completed' &&
                    in_array($status, ['paid', 'completed', 'cancelled'], true) &&
                    $statusKey !== $status
                ) {
                    return false;
                }

                return $this->matchesBookingSearch($booking, $q);
            })->values();
        } catch (\Throwable $e) {
            report($e);
            $loadError = 'Unable to load booking history right now.';
        }

        return view('provider.bookings_history', compact('bookings', 'q', 'status', 'from', 'to', 'loadError'));
    }

    public function show(string $reference_code)
    {
        $providerId = $this->providerId();
        if (!$this->bookingsTableAvailable()) {
            return redirect()->route('provider.bookings')
                ->withErrors(['general' => 'Booking details are not available right now.']);
        }

        try {
            $booking = $this->enrichBookings(
                DB::table('bookings as b')
                    ->where('b.provider_id', $providerId)
                    ->where('b.reference_code', $reference_code)
                    ->select(
                        'b.reference_code',
                        $this->columnOrDefault('bookings', 'id', 'b'),
                        $this->columnOrDefault('bookings', 'customer_id', 'b'),
                        $this->columnOrDefault('bookings', 'provider_id', 'b'),
                        $this->columnOrDefault('bookings', 'service_id', 'b'),
                        $this->columnOrDefault('bookings', 'service_option_id', 'b'),
                        $this->columnOrDefault('bookings', 'booking_date', 'b'),
                        $this->columnOrDefault('bookings', 'requested_start_time', 'b'),
                        $this->columnOrDefault('bookings', 'time_start', 'b'),
                        $this->columnOrDefault('bookings', 'time_end', 'b'),
                        'b.status',
                        $this->columnOrDefault('bookings', 'price', 'b', null, '0'),
                        $this->columnOrDefault('bookings', 'address', 'b'),
                        $this->columnOrDefault('bookings', 'contact_phone', 'b'),
                        $this->columnOrDefault('bookings', 'created_at', 'b'),
                        $this->columnOrDefault('bookings', 'updated_at', 'b'),
                        $this->selectBookingLocationColumn('customer_latitude'),
                        $this->selectBookingLocationColumn('customer_longitude'),
                        $this->selectBookingLocationColumn('formatted_address')
                    )
                    ->limit(1)
                    ->get()
            )->first();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('provider.bookings')
                ->withErrors(['general' => 'Unable to load booking details right now.']);
        }

        abort_if(!$booking, 404);

        $booking->customer_name = $booking->name;
        $booking->customer_email = $booking->email;
        $booking->customer_phone = $booking->phone;
        $booking->service_name = $booking->service;
        $booking->option_label = $booking->option ?? '';
        $booking->tracking_enabled = $this->bookingCanTrack($booking->status_key ?? $booking->status);

        if (!$booking->tracking_enabled && $booking->id && $this->providerLocationTableAvailable()) {
            DB::table('booking_provider_locations')
                ->where('booking_id', (int) $booking->id)
                ->where('is_tracking', true)
                ->update([
                    'is_tracking' => false,
                    'stopped_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        $booking->provider_location = $booking->tracking_enabled && $booking->id
            ? $this->latestProviderLocation((int) $booking->id)
            : null;

        return view('provider.bookings.show', compact('booking'));
    }

    public function updateLocation(Request $request, string $reference, GeoapifyService $geoapify): JsonResponse
    {
        $providerId = $this->providerId();

        $payload = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $booking = $this->bookingByReference($reference, $providerId);
        abort_if(!$booking, 404);

        if (!$this->bookingCanTrack($booking->status ?? null) || !$this->providerLocationTableAvailable()) {
            return response()->json([
                'message' => 'Live tracking is not available for this booking right now.',
            ], 422);
        }

        $formattedAddress = null;
        if ($geoapify->configured()) {
            try {
                $reverse = $geoapify->reverseGeocode(
                    (float) $payload['latitude'],
                    (float) $payload['longitude']
                );

                $formattedAddress = $reverse['formatted'] ?? null;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        DB::table('booking_provider_locations')->updateOrInsert(
            ['booking_id' => $booking->id],
            [
                'provider_id' => $providerId,
                'latitude' => $payload['latitude'],
                'longitude' => $payload['longitude'],
                'formatted_address' => $formattedAddress,
                'is_tracking' => true,
                'tracked_at' => now(),
                'stopped_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'ok' => true,
            'location' => [
                'latitude' => (float) $payload['latitude'],
                'longitude' => (float) $payload['longitude'],
                'formatted_address' => $formattedAddress,
                'tracked_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    public function stopLocationTracking(string $reference): JsonResponse
    {
        $providerId = $this->providerId();
        $booking = $this->bookingByReference($reference, $providerId);
        abort_if(!$booking, 404);

        if ($this->providerLocationTableAvailable()) {
            DB::table('booking_provider_locations')
                ->where('booking_id', $booking->id)
                ->update([
                    'is_tracking' => false,
                    'stopped_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'ok' => true,
        ]);
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
            'pending'     => ['confirmed', 'cancelled', 'in_progress'],
            'confirmed'   => ['in_progress', 'cancelled'],
            'in_progress' => ['paid', 'cancelled'],
            'paid'        => ['completed'],
            'completed'   => [],
            'cancelled'   => [],
        ];

        $current = $this->normalizeStatusKey($booking->status);
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

            // Close live tracking once the booking leaves its active tracking states.
            if (!$this->bookingCanTrack($next) && $this->providerLocationTableAvailable()) {
                DB::table('booking_provider_locations')
                    ->where('booking_id', $booking->id)
                    ->update([
                        'is_tracking' => false,
                        'stopped_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

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

            if (
                Schema::hasTable('notifications') &&
                Schema::hasColumns('notifications', ['user_id', 'reference_code', 'message', 'is_read'])
            ) {
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
            }
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
