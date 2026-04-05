<?php

namespace App\Http\Controllers;

use App\Services\GeoapifyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProviderBookingController extends Controller
{
    private const ADJUSTMENT_MAX_INCREASE_PERCENT = 35.0;
    private const HEAVY_SOILING_AUTO_PERCENT = 10.0;
    private const HEAVY_SOILING_MIN_FEE = 300.0;

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

    private function providerLocationTimestampsAvailable(): bool
    {
        return Schema::hasTable('booking_provider_locations')
            && Schema::hasColumns('booking_provider_locations', ['created_at', 'updated_at']);
    }

    private function providerAvailabilityTimestampsAvailable(): bool
    {
        return Schema::hasTable('provider_availability')
            && Schema::hasColumns('provider_availability', ['created_at', 'updated_at']);
    }

    private function bookingAdjustmentTableAvailable(): bool
    {
        return $this->tableHasColumns('booking_adjustments', [
            'id',
            'booking_id',
            'provider_id',
            'customer_id',
            'original_service_name',
            'original_option_summary',
            'original_price',
            'proposed_service_name',
            'proposed_service_option_id',
            'proposed_option_ids_payload',
            'proposed_scope_summary',
            'additional_fee',
            'proposed_total',
            'price_increase_percent',
            'reason_payload',
            'other_reason',
            'provider_note',
            'customer_response_note',
            'evidence_path',
            'evidence_name',
            'evidence_mime',
            'status',
        ]);
    }

    private function bookingAdjustmentLogTableAvailable(): bool
    {
        return $this->tableHasColumns('booking_adjustment_logs', [
            'booking_adjustment_id',
            'booking_id',
            'actor_role',
            'actor_id',
            'action',
            'note',
            'payload',
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
                $this->columnOrDefault('bookings', 'cancellation_reason', 'b'),
                $this->columnOrDefault('bookings', 'cancelled_by_role', 'b'),
                $this->columnOrDefault('bookings', 'adjustment_status', 'b'),
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
                'customer_id',
                'provider_id',
                'reference_code',
                'booking_date',
                'time_start',
                'time_end',
                'status',
                Schema::hasColumn('bookings', 'cancellation_reason')
                    ? 'cancellation_reason'
                    : DB::raw('NULL as cancellation_reason'),
                Schema::hasColumn('bookings', 'cancelled_by_role')
                    ? 'cancelled_by_role'
                    : DB::raw('NULL as cancelled_by_role'),
                Schema::hasColumn('bookings', 'adjustment_status')
                    ? 'adjustment_status'
                    : DB::raw('NULL as adjustment_status'),
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

        $columns = [
            'latitude',
            'longitude',
            'formatted_address',
            'is_tracking',
            'tracked_at',
            'stopped_at',
        ];

        if (Schema::hasColumn('booking_provider_locations', 'updated_at')) {
            $columns[] = 'updated_at';
        }

        return DB::table('booking_provider_locations')
            ->where('booking_id', $bookingId)
            ->first($columns);
    }

    private function stopTrackingForBooking(int $bookingId): void
    {
        if (!$bookingId || !$this->providerLocationTableAvailable()) {
            return;
        }

        $payload = [
            'is_tracking' => false,
            'stopped_at' => now(),
        ];

        if (Schema::hasColumn('booking_provider_locations', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        try {
            DB::table('booking_provider_locations')
                ->where('booking_id', $bookingId)
                ->update($payload);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function bookingAdjustmentByBookingId(int $bookingId): ?object
    {
        if (!$this->bookingAdjustmentTableAvailable()) {
            return null;
        }

        return DB::table('booking_adjustments')
            ->where('booking_id', $bookingId)
            ->first();
    }

    private function normalizeAdjustmentEvidencePath($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = str_replace('\\', '/', trim((string) $value));

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $value = parse_url($value, PHP_URL_PATH) ?: $value;
        }

        $value = ltrim($value, '/');

        if (Str::startsWith($value, 'storage/')) {
            $value = substr($value, 8);
        }

        if (Str::startsWith($value, 'booking-adjustments/evidence/')) {
            return $value;
        }

        return 'booking-adjustments/evidence/' . basename($value);
    }

    private function normalizeTime(string $time): string
    {
        $time = trim($time);

        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        return $time;
    }

    private function adjustmentReasonLabel(string $code): string
    {
        return match ($this->normalizeStatusKey($code)) {
            'larger_area' => 'Larger area than declared',
            'additional_rooms' => 'Additional rooms or sections',
            'heavy_soiling' => 'Heavily soiled or deep cleaning required',
            'other' => 'Other onsite issue',
            default => ucwords(str_replace('_', ' ', $code)),
        };
    }

    private function formatAdjustmentRecord(?object $adjustment): ?object
    {
        if (!$adjustment) {
            return null;
        }

        $adjustment->proposed_option_ids = collect(json_decode((string) ($adjustment->proposed_option_ids_payload ?? '[]'), true))
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->values()
            ->all();

        $adjustment->reason_codes = collect(json_decode((string) ($adjustment->reason_payload ?? '[]'), true))
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->values()
            ->all();

        $adjustment->reason_labels = collect($adjustment->reason_codes)
            ->map(fn ($code) => $this->adjustmentReasonLabel((string) $code))
            ->values()
            ->all();

        $adjustment->status_key = trim((string) ($adjustment->status ?? ''));
        $adjustment->status_label = ucwords(str_replace('_', ' ', $adjustment->status_key));
        $adjustment->original_price_display = number_format((float) ($adjustment->original_price ?? 0), 2);
        $adjustment->additional_fee_display = number_format((float) ($adjustment->additional_fee ?? 0), 2);
        $adjustment->proposed_total_display = number_format((float) ($adjustment->proposed_total ?? 0), 2);
        $adjustment->difference_display = number_format(
            (float) (($adjustment->proposed_total ?? 0) - ($adjustment->original_price ?? 0)),
            2
        );
        $adjustment->price_increase_percent_display = number_format((float) ($adjustment->price_increase_percent ?? 0), 1);
        $adjustment->evidence_url = !empty($adjustment->evidence_path)
            ? route('booking.adjustments.attachment', ['filename' => basename((string) $adjustment->evidence_path)])
            : null;
        $adjustment->resolved_at_label = !empty($adjustment->resolved_at)
            ? Carbon::parse($adjustment->resolved_at)->format('M d, Y h:i A')
            : null;

        return $adjustment;
    }

    private function formattedAdjustmentLogs(int $adjustmentId)
    {
        if (!$this->tableHasColumns('booking_adjustment_logs', [
            'booking_adjustment_id',
            'booking_id',
            'actor_role',
            'actor_id',
            'action',
            'payload',
            'created_at',
        ])) {
            return collect();
        }

        return DB::table('booking_adjustment_logs')
            ->where('booking_adjustment_id', $adjustmentId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($log) {
                $payload = json_decode((string) ($log->payload ?? '[]'), true);
                $payload = is_array($payload) ? $payload : [];

                $log->actor_label = match (Str::lower(trim((string) ($log->actor_role ?? '')))) {
                    'provider' => 'Provider',
                    'customer' => 'Customer',
                    'admin' => 'Admin',
                    default => 'System',
                };
                $log->action_label = $this->adjustmentActionLabel((string) ($log->action ?? ''));
                $log->detail = $this->adjustmentLogDetail((string) ($log->action ?? ''), $payload);
                $log->created_at_label = !empty($log->created_at)
                    ? Carbon::parse($log->created_at)->format('M d, Y h:i A')
                    : null;

                return $log;
            })
            ->values();
    }

    private function adjustmentActionLabel(string $action): string
    {
        return match (Str::lower(trim($action))) {
            'created' => 'Adjustment sent',
            'updated' => 'Adjustment updated',
            'provider_note' => 'Provider note',
            'customer_note' => 'Customer note',
            'accepted' => 'Adjustment accepted',
            'revision_requested' => 'Customer asked for a review',
            'rejected' => 'Adjustment rejected',
            'cancelled_booking' => 'Booking cancelled',
            default => ucwords(str_replace('_', ' ', trim($action))),
        };
    }

    private function adjustmentLogDetail(string $action, array $payload): ?string
    {
        $normalizedAction = Str::lower(trim($action));

        return match ($normalizedAction) {
            'created', 'updated' => $this->firstFilled([
                $this->priceChangeSummary($payload, 'original_price', 'proposed_total'),
                !empty($payload['reference_code']) ? 'Ref: ' . $payload['reference_code'] : null,
            ]),
            'provider_note' => $this->firstFilled([
                isset($payload['requested_total'])
                    ? 'Provider sent a note while the requested total is PHP ' . number_format((float) $payload['requested_total'], 2) . '.'
                    : null,
                !empty($payload['reference_code']) ? 'Ref: ' . $payload['reference_code'] : null,
            ]),
            'customer_note' => $this->firstFilled([
                isset($payload['requested_total'])
                    ? 'Customer sent a note about the requested total of PHP ' . number_format((float) $payload['requested_total'], 2) . '.'
                    : null,
                !empty($payload['reference_code']) ? 'Ref: ' . $payload['reference_code'] : null,
            ]),
            'accepted' => $this->firstFilled([
                isset($payload['new_price'])
                    ? 'Customer approved the updated total of PHP ' . number_format((float) $payload['new_price'], 2) . '.'
                    : null,
                $this->priceChangeSummary($payload, 'old_price', 'new_price'),
            ]),
            'revision_requested' => $this->firstFilled([
                isset($payload['requested_total'])
                    ? 'Customer asked for another review before accepting PHP ' . number_format((float) $payload['requested_total'], 2) . '.'
                    : null,
                isset($payload['kept_price'])
                    ? 'Customer is still holding the original total of PHP ' . number_format((float) $payload['kept_price'], 2) . '.'
                    : null,
            ]),
            'rejected' => $this->firstFilled([
                isset($payload['kept_price']) && isset($payload['rejected_total'])
                    ? 'Customer kept PHP ' . number_format((float) $payload['kept_price'], 2)
                        . ' and rejected PHP ' . number_format((float) $payload['rejected_total'], 2) . '.'
                    : null,
                isset($payload['kept_price'])
                    ? 'Customer kept the original total of PHP ' . number_format((float) $payload['kept_price'], 2) . '.'
                    : null,
            ]),
            'cancelled_booking' => !empty($payload['cancellation_reason'])
                ? 'Reason: ' . trim((string) $payload['cancellation_reason'])
                : 'The booking was cancelled while the adjustment was still pending.',
            default => null,
        };
    }

    private function priceChangeSummary(array $payload, string $fromKey, string $toKey): ?string
    {
        if (!isset($payload[$fromKey], $payload[$toKey])) {
            return null;
        }

        return 'PHP '
            . number_format((float) $payload[$fromKey], 2)
            . ' -> PHP '
            . number_format((float) $payload[$toKey], 2);
    }

    private function restoreAvailabilitySlot(object $booking): void
    {
        if (!Schema::hasTable('provider_availability')) {
            return;
        }

        $providerId = (int) ($booking->provider_id ?? 0);
        $bookingDate = trim((string) ($booking->booking_date ?? ''));
        $slotStart = $this->normalizeTime((string) ($booking->time_start ?? ''));
        $slotEnd = $this->normalizeTime((string) ($booking->time_end ?? ''));

        if (!$providerId || $bookingDate === '' || $slotStart === '' || $slotEnd === '') {
            return;
        }

        $today = now(config('app.timezone') ?? 'Asia/Manila')->toDateString();
        if ($bookingDate < $today) {
            return;
        }

        $conflictingBookingExists = DB::table('bookings')
            ->where('provider_id', $providerId)
            ->whereDate('booking_date', $bookingDate)
            ->where('id', '!=', $booking->id)
            ->get(['status', 'time_start', 'time_end'])
            ->contains(function ($row) use ($slotStart, $slotEnd) {
                $status = $this->normalizeStatusKey((string) ($row->status ?? ''));

                return $status !== 'cancelled'
                    && $this->normalizeTime((string) ($row->time_start ?? '')) === $slotStart
                    && $this->normalizeTime((string) ($row->time_end ?? '')) === $slotEnd;
            });

        if ($conflictingBookingExists) {
            return;
        }

        $matchingSlot = DB::table('provider_availability')
            ->where('provider_id', $providerId)
            ->whereDate('date', $bookingDate)
            ->get()
            ->first(function ($slot) use ($slotStart, $slotEnd) {
                return $this->normalizeTime((string) ($slot->time_start ?? '')) === $slotStart
                    && $this->normalizeTime((string) ($slot->time_end ?? '')) === $slotEnd;
            });

        if ($matchingSlot) {
            $payload = ['status' => 'active'];

            if (Schema::hasColumn('provider_availability', 'updated_at')) {
                $payload['updated_at'] = now();
            }

            DB::table('provider_availability')
                ->where('id', $matchingSlot->id)
                ->update($payload);

            return;
        }

        $payload = [
            'provider_id' => $providerId,
            'date' => $bookingDate,
            'time_start' => $slotStart,
            'time_end' => $slotEnd,
            'status' => 'active',
        ];

        if ($this->providerAvailabilityTimestampsAvailable()) {
            $payload['created_at'] = now();
            $payload['updated_at'] = now();
        }

        DB::table('provider_availability')->insert($payload);
    }

    private function insertCustomerNotification(
        int $customerId,
        string $referenceCode,
        string $message,
        ?string $type = null
    ): void {
        if ($customerId <= 0 || trim($referenceCode) === '' || trim($message) === '') {
            return;
        }

        if (
            !Schema::hasTable('notifications') ||
            !Schema::hasColumns('notifications', ['user_id', 'reference_code', 'message', 'is_read'])
        ) {
            return;
        }

        $payload = [
            'user_id' => $customerId,
            'reference_code' => $referenceCode,
            'message' => $message,
            'is_read' => 0,
        ];

        if ($type !== null && Schema::hasColumn('notifications', 'type')) {
            $payload['type'] = $type;
        }

        if (Schema::hasColumn('notifications', 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn('notifications', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        try {
            DB::table('notifications')->insert($payload);
        } catch (\Throwable $exception) {
            report($exception);
        }
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

    private function bookingSelectedOptionIds(int $bookingId, $fallbackOptionId = null): array
    {
        $ids = collect();

        if ($this->tableHasColumns('booking_service_options', ['booking_id', 'service_option_id'])) {
            $ids = DB::table('booking_service_options')
                ->where('booking_id', $bookingId)
                ->pluck('service_option_id');
        }

        if ($ids->isEmpty() && $fallbackOptionId) {
            $ids = collect([(int) $fallbackOptionId]);
        }

        return $ids
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function serviceOptionsForService(int $serviceId)
    {
        if (!$serviceId || !$this->tableHasColumns('service_options', ['id', 'service_id', 'label', 'price_addition'])) {
            return collect();
        }

        return DB::table('service_options')
            ->where('service_id', $serviceId)
            ->orderBy('label')
            ->get(['id', 'service_id', 'label', 'price_addition']);
    }

    private function specificAreaServiceId(): ?int
    {
        if (!Schema::hasTable('services') || !Schema::hasColumn('services', 'name')) {
            return null;
        }

        $id = DB::table('services')
            ->whereRaw('LOWER(name) = ?', ['specific area cleaning'])
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function optionSummaryFromIds(array $optionIds, $optionsById): ?string
    {
        $labels = collect($optionIds)
            ->map(fn ($id) => $optionsById->get((int) $id)?->label ?? null)
            ->filter(fn ($label) => is_string($label) && trim($label) !== '')
            ->unique()
            ->values();

        return $labels->isNotEmpty() ? $labels->implode(', ') : null;
    }

    private function optionIdsMatch(array $left, array $right): bool
    {
        sort($left);
        sort($right);

        return array_values($left) === array_values($right);
    }

    private function automaticReasonFee(array $reasonCodes, float $originalPrice): float
    {
        $reasonCodes = collect($reasonCodes)
            ->map(fn ($code) => $this->normalizeStatusKey((string) $code))
            ->filter()
            ->values()
            ->all();

        if (!in_array('heavy_soiling', $reasonCodes, true)) {
            return 0.0;
        }

        return round(max(
            self::HEAVY_SOILING_MIN_FEE,
            $originalPrice * (self::HEAVY_SOILING_AUTO_PERCENT / 100)
        ), 2);
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
        $customerRatings = collect();

        if (
            Schema::hasTable('customer_ratings') &&
            Schema::hasColumns('customer_ratings', ['booking_id', 'id'])
        ) {
            $bookingIds = $bookings->pluck('id')
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->unique()
                ->values();

            if ($bookingIds->isNotEmpty()) {
                $ratingColumns = ['id', 'booking_id'];

                if (Schema::hasColumn('customer_ratings', 'editable_until')) {
                    $ratingColumns[] = 'editable_until';
                }

                if (Schema::hasColumn('customer_ratings', 'edit_count')) {
                    $ratingColumns[] = 'edit_count';
                }

                $customerRatings = DB::table('customer_ratings')
                    ->whereIn('booking_id', $bookingIds)
                    ->get($ratingColumns)
                    ->keyBy('booking_id');
            }
        }

        return $bookings->map(function ($booking) use ($customers, $services, $options, $areasMap, $customerRatings) {
            $customer = $customers->get($booking->customer_id);
            $service = $services->get($booking->service_id);
            $option = $options->get($booking->service_option_id);
            $customerRating = $customerRatings->get($booking->id);
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
            $booking->customer_rating_id = $customerRating->id ?? null;
            $booking->has_customer_rating = $customerRating !== null;
            $booking->customer_rating_edit_count = (int) ($customerRating->edit_count ?? 0);
            $booking->customer_rating_editable_until = $customerRating->editable_until ?? null;
            $booking->can_edit_customer_rating = $customerRating
                && !empty($customerRating->editable_until)
                && now()->lt(Carbon::parse($customerRating->editable_until));
            $booking->show_url = null;
            $booking->customer_rating_url = null;
            $booking->customer_rating_label = null;
            $booking->customer_rating_class = null;

            try {
                if (
                    Route::has('provider.bookings.show') &&
                    !empty($booking->reference_code)
                ) {
                    $booking->show_url = route('provider.bookings.show', $booking->reference_code);
                }
            } catch (\Throwable $exception) {
                report($exception);
            }

            try {
                $canOpenRating = Route::has('provider.customer-ratings')
                    && !empty($booking->id)
                    && in_array($statusKey, ['completed', 'paid'], true);

                if ($canOpenRating) {
                    $booking->customer_rating_url = route('provider.customer-ratings', [
                        'booking' => $booking->id,
                    ]);

                    if (!$booking->has_customer_rating) {
                        $booking->customer_rating_label = 'Rate Customer';
                        $booking->customer_rating_class = 'btn-rate';
                    } elseif ($booking->can_edit_customer_rating) {
                        $booking->customer_rating_label = 'Edit Rating';
                        $booking->customer_rating_class = 'btn-rate edit';
                    } else {
                        $booking->customer_rating_label = 'View Rating';
                        $booking->customer_rating_class = 'btn-rate view';
                    }
                }
            } catch (\Throwable $exception) {
                report($exception);
            }

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
                        $this->columnOrDefault('bookings', 'cancellation_reason', 'b'),
                        $this->columnOrDefault('bookings', 'cancelled_by_role', 'b'),
                        $this->columnOrDefault('bookings', 'adjustment_status', 'b'),
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

        if (!$booking->tracking_enabled && $booking->id) {
            $this->stopTrackingForBooking((int) $booking->id);
        }

        $booking->provider_location = $booking->tracking_enabled && $booking->id
            ? $this->latestProviderLocation((int) $booking->id)
            : null;
        $adjustment = $this->formatAdjustmentRecord(
            $this->bookingAdjustmentByBookingId((int) $booking->id)
        );

        $serviceOptions = $this->serviceOptionsForService((int) ($booking->service_id ?? 0))
            ->map(function ($option) {
                return (object) [
                    'id' => (int) $option->id,
                    'label' => trim((string) $option->label),
                    'price_addition' => (float) ($option->price_addition ?? 0),
                ];
            })
            ->values();

        $serviceOptionsById = $serviceOptions->keyBy('id');
        $currentOptionIds = $this->bookingSelectedOptionIds((int) ($booking->id ?? 0), $booking->service_option_id ?? null);
        $requestedOptionIds = collect($adjustment->proposed_option_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $serviceOptionsById->has($id))
            ->values()
            ->all();

        if (empty($requestedOptionIds) && !empty($adjustment->proposed_service_option_id) && $serviceOptionsById->has((int) $adjustment->proposed_service_option_id)) {
            $requestedOptionIds = [(int) $adjustment->proposed_service_option_id];
        }

        if (empty($requestedOptionIds)) {
            $requestedOptionIds = $currentOptionIds;
        }

        $isSpecificAreaBooking = (int) ($booking->service_id ?? 0) !== 0
            && (int) ($booking->service_id ?? 0) === (int) ($this->specificAreaServiceId() ?? 0);

        $originalOptionSummary = $this->optionSummaryFromIds($currentOptionIds, $serviceOptionsById)
            ?: trim((string) ($booking->option_label ?? ''));
        $requestedOptionSummary = $this->optionSummaryFromIds($requestedOptionIds, $serviceOptionsById)
            ?: $originalOptionSummary;
        $adjustmentLogs = $adjustment
            ? $this->formattedAdjustmentLogs((int) ($adjustment->id ?? 0))
            : collect();

        return view('provider.bookings.show', compact(
            'booking',
            'adjustment',
            'adjustmentLogs',
            'serviceOptions',
            'currentOptionIds',
            'requestedOptionIds',
            'isSpecificAreaBooking',
            'originalOptionSummary',
            'requestedOptionSummary'
        ));
    }

    public function adjustmentEvidence(string $filename)
    {
        $path = $this->normalizeAdjustmentEvidencePath($filename);

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    public function sendAdjustmentNote(Request $request, string $reference)
    {
        $providerId = $this->providerId();

        if (!$this->bookingAdjustmentTableAvailable()) {
            return back()->withErrors([
                'general' => 'Booking adjustments are not available right now.',
            ]);
        }

        $data = $request->validate([
            'provider_adjustment_note' => ['required', 'string', 'max:1200'],
        ]);

        try {
            DB::beginTransaction();

            $booking = DB::table('bookings')
                ->where('provider_id', $providerId)
                ->where('reference_code', $reference)
                ->lockForUpdate()
                ->first([
                    'id',
                    'customer_id',
                    'reference_code',
                    'price',
                    'status',
                    Schema::hasColumn('bookings', 'adjustment_status')
                        ? 'adjustment_status'
                        : DB::raw('NULL as adjustment_status'),
                ]);

            if (!$booking) {
                DB::rollBack();
                abort(404);
            }

            $adjustment = DB::table('booking_adjustments')
                ->where('booking_id', $booking->id)
                ->lockForUpdate()
                ->first();

            if (!$adjustment || ($adjustment->status ?? '') !== 'pending_adjustment_approval') {
                DB::rollBack();

                return back()->withErrors([
                    'provider_adjustment_note' => 'There is no pending adjustment to reply to right now.',
                ])->withInput();
            }

            $note = trim((string) ($data['provider_adjustment_note'] ?? ''));

            DB::table('booking_adjustments')
                ->where('id', $adjustment->id)
                ->update([
                    'provider_note' => $note,
                    'updated_at' => now(),
                ]);

            DB::table('bookings')
                ->where('id', $booking->id)
                ->update([
                    'adjustment_status' => 'pending_adjustment_approval',
                    'updated_at' => now(),
                ]);

            $this->logBookingAdjustmentActivity(
                (int) $adjustment->id,
                (int) $booking->id,
                'provider',
                $providerId,
                'provider_note',
                $note,
                [
                    'reference_code' => $booking->reference_code,
                    'requested_total' => (float) ($adjustment->proposed_total ?? 0),
                ]
            );

            $this->insertCustomerNotification(
                (int) $booking->customer_id,
                (string) $booking->reference_code,
                'The provider sent a note about the booking adjustment for ref ' . $booking->reference_code
                    . '. Current requested total is PHP ' . number_format((float) ($adjustment->proposed_total ?? 0), 2)
                    . '. Note: ' . $note,
                'booking_adjustment'
            );

            DB::commit();

            return redirect()
                ->route('provider.bookings.show', $booking->reference_code)
                ->with('success', 'Your note was sent to the customer.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->withErrors([
                'provider_adjustment_note' => 'Unable to send your note right now. Please try again.',
            ])->withInput();
        }
    }

    public function submitAdjustment(Request $request, string $reference)
    {
        $providerId = $this->providerId();

        if (!$this->bookingAdjustmentTableAvailable()) {
            return back()->withErrors([
                'general' => 'Booking adjustments are not available right now.',
            ]);
        }

        $data = $request->validate([
            'reason_codes' => ['required', 'array', 'min:1'],
            'reason_codes.*' => ['required', 'string', 'in:larger_area,additional_rooms,heavy_soiling,other'],
            'other_reason' => ['nullable', 'string', 'max:600'],
            'corrected_option_id' => ['nullable', 'integer'],
            'corrected_option_ids' => ['nullable', 'array', 'min:1'],
            'corrected_option_ids.*' => ['integer', 'distinct'],
            'provider_note' => ['nullable', 'string', 'max:1200'],
            'evidence' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        try {
            DB::beginTransaction();

            $booking = DB::table('bookings as b')
                ->join('services as s', 's.id', '=', 'b.service_id')
                ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
                ->leftJoinSub($this->bookingAreasSubquery(), 'areas', function ($join) {
                    $join->on('areas.booking_id', '=', 'b.id');
                })
                ->where('b.provider_id', $providerId)
                ->where('b.reference_code', $reference)
                ->lockForUpdate()
                ->select(
                    'b.id',
                    'b.provider_id',
                    'b.customer_id',
                    'b.reference_code',
                    'b.status',
                    'b.price',
                    'b.service_id',
                    'b.service_option_id',
                    's.name as service_name',
                    's.base_price',
                    DB::raw("COALESCE(areas.areas_label, o.label) as option_name")
                )
                ->first();

            if (!$booking) {
                DB::rollBack();
                abort(404);
            }

            if ($this->normalizeStatusKey((string) ($booking->status ?? '')) !== 'in_progress') {
                DB::rollBack();

                return back()->withErrors([
                    'general' => 'Mismatch reporting is only available while the booking is in progress.',
                ])->withInput();
            }

            $reasonCodes = collect($data['reason_codes'] ?? [])
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (in_array('other', $reasonCodes, true) && trim((string) ($data['other_reason'] ?? '')) === '') {
                DB::rollBack();

                return back()->withErrors([
                    'other_reason' => 'Please describe the other onsite issue.',
                ])->withInput();
            }

            $serviceOptions = $this->serviceOptionsForService((int) ($booking->service_id ?? 0))->keyBy('id');

            if ($serviceOptions->isEmpty()) {
                DB::rollBack();

                return back()->withErrors([
                    'general' => 'This booking has no adjustable scope options right now.',
                ])->withInput();
            }

            $isSpecificAreaBooking = (int) ($booking->service_id ?? 0) !== 0
                && (int) ($booking->service_id ?? 0) === (int) ($this->specificAreaServiceId() ?? 0);

            $originalOptionIds = collect($this->bookingSelectedOptionIds(
                (int) $booking->id,
                $booking->service_option_id ?? null
            ))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $serviceOptions->has($id))
                ->values()
                ->all();

            $scopeChangeRequested = collect($reasonCodes)
                ->contains(fn ($code) => in_array($this->normalizeStatusKey((string) $code), ['larger_area', 'additional_rooms'], true));

            $correctedOptionIds = $isSpecificAreaBooking
                ? collect($data['corrected_option_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $serviceOptions->has($id))
                    ->unique()
                    ->values()
                    ->all()
                : collect([(int) ($data['corrected_option_id'] ?? 0)])
                    ->filter(fn ($id) => $serviceOptions->has($id))
                    ->values()
                    ->all();

            if (!$scopeChangeRequested) {
                $correctedOptionIds = $originalOptionIds;
            }

            if (empty($correctedOptionIds) && $scopeChangeRequested) {
                DB::rollBack();

                return back()->withErrors([
                    $isSpecificAreaBooking ? 'corrected_option_ids' : 'corrected_option_id' => $isSpecificAreaBooking
                        ? 'Please select the corrected sections for this booking.'
                        : 'Please select the corrected size or option for this booking.',
                ])->withInput();
            }

            if ($scopeChangeRequested && $this->optionIdsMatch($correctedOptionIds, $originalOptionIds)) {
                DB::rollBack();

                return back()->withErrors([
                    $isSpecificAreaBooking ? 'corrected_option_ids' : 'corrected_option_id' => $isSpecificAreaBooking
                        ? 'Choose the corrected sections so the booking matches the actual onsite scope.'
                        : 'Choose the corrected size or option so the booking matches the actual onsite scope.',
                ])->withInput();
            }

            $originalOptionTotal = round(collect($originalOptionIds)->sum(function ($id) use ($serviceOptions) {
                return (float) ($serviceOptions->get((int) $id)->price_addition ?? 0);
            }), 2);
            $correctedOptionTotal = round(collect($correctedOptionIds)->sum(function ($id) use ($serviceOptions) {
                return (float) ($serviceOptions->get((int) $id)->price_addition ?? 0);
            }), 2);

            $originalPrice = round((float) ($booking->price ?? 0), 2);
            $basePrice = round((float) ($booking->base_price ?? ($originalPrice - $originalOptionTotal)), 2);
            if ($basePrice < 0) {
                $basePrice = 0;
            }

            $automaticReasonFee = $this->automaticReasonFee($reasonCodes, $originalPrice);
            $proposedTotal = round($basePrice + $correctedOptionTotal + $automaticReasonFee, 2);
            $additionalFee = round($proposedTotal - $originalPrice, 2);

            if ($proposedTotal < $originalPrice) {
                DB::rollBack();

                return back()->withErrors([
                    $isSpecificAreaBooking ? 'corrected_option_ids' : 'corrected_option_id' => 'The corrected selection cannot reduce the original booking total.',
                ])->withInput();
            }

            $increasePercent = $originalPrice > 0
                ? round((($proposedTotal - $originalPrice) / $originalPrice) * 100, 2)
                : 0.0;

            if ($increasePercent > self::ADJUSTMENT_MAX_INCREASE_PERCENT) {
                DB::rollBack();

                return back()->withErrors([
                    $isSpecificAreaBooking ? 'corrected_option_ids' : 'corrected_option_id' => 'Price adjustments cannot increase by more than ' . (int) self::ADJUSTMENT_MAX_INCREASE_PERCENT . '%.',
                ])->withInput();
            }

            $existingAdjustment = DB::table('booking_adjustments')
                ->where('booking_id', $booking->id)
                ->lockForUpdate()
                ->first();

            if ($existingAdjustment && ($existingAdjustment->status ?? '') === 'adjustment_accepted') {
                DB::rollBack();

                return back()->withErrors([
                    'general' => 'This booking adjustment was already accepted and cannot be changed again.',
                ])->withInput();
            }

            $file = $request->file('evidence');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = 'adjustment_' . $providerId . '_' . $booking->id . '_' . Str::uuid() . '.' . $extension;
            $path = str_replace('\\', '/', $file->storeAs('booking-adjustments/evidence', $filename, 'public'));

            if ($existingAdjustment && !empty($existingAdjustment->evidence_path)) {
                $oldPath = $this->normalizeAdjustmentEvidencePath($existingAdjustment->evidence_path);

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $originalOptionSummary = $this->optionSummaryFromIds($originalOptionIds, $serviceOptions)
                ?: trim((string) ($booking->option_name ?? ''))
                ?: null;
            $correctedOptionSummary = $this->optionSummaryFromIds($correctedOptionIds, $serviceOptions)
                ?: $originalOptionSummary;

            $scopeParts = [];
            if ($correctedOptionSummary) {
                $scopeParts[] = 'Updated selection: ' . $correctedOptionSummary;
            }

            if ($originalOptionSummary && $correctedOptionSummary && $originalOptionSummary !== $correctedOptionSummary) {
                $scopeParts[] = 'Originally booked: ' . $originalOptionSummary;
            }

            if (!empty($reasonCodes)) {
                $scopeParts[] = 'Reason: ' . collect($reasonCodes)
                    ->map(fn ($code) => $this->adjustmentReasonLabel((string) $code))
                    ->implode(', ');
            }

            if ($automaticReasonFee > 0) {
                $scopeParts[] = 'Automatic condition fee: PHP ' . number_format($automaticReasonFee, 2);
            }

            if (!empty($data['other_reason'])) {
                $scopeParts[] = 'Other note: ' . trim((string) $data['other_reason']);
            }

            $proposedScopeSummary = implode('. ', array_filter($scopeParts));

            $payload = [
                'booking_id' => $booking->id,
                'provider_id' => $providerId,
                'customer_id' => $booking->customer_id,
                'original_service_name' => $booking->service_name,
                'original_option_summary' => $originalOptionSummary,
                'original_price' => $originalPrice,
                'proposed_service_name' => $booking->service_name,
                'proposed_service_option_id' => $correctedOptionIds[0] ?? null,
                'proposed_option_ids_payload' => json_encode(array_values($correctedOptionIds)),
                'proposed_scope_summary' => $proposedScopeSummary,
                'additional_fee' => $additionalFee,
                'proposed_total' => $proposedTotal,
                'price_increase_percent' => $increasePercent,
                'reason_payload' => json_encode($reasonCodes),
                'other_reason' => trim((string) ($data['other_reason'] ?? '')) ?: null,
                'provider_note' => trim((string) ($data['provider_note'] ?? '')) ?: null,
                'evidence_path' => $path,
                'evidence_name' => $file->getClientOriginalName(),
                'evidence_mime' => $file->getClientMimeType(),
                'status' => 'pending_adjustment_approval',
                'resolved_at' => null,
                'updated_at' => now(),
            ];

            if ($existingAdjustment) {
                DB::table('booking_adjustments')
                    ->where('id', $existingAdjustment->id)
                    ->update($payload);

                $adjustmentId = (int) $existingAdjustment->id;
                $action = 'updated';
            } else {
                $payload['created_at'] = now();
                $adjustmentId = DB::table('booking_adjustments')->insertGetId($payload);
                $action = 'created';
            }

            DB::table('bookings')
                ->where('id', $booking->id)
                ->update([
                    'adjustment_status' => 'pending_adjustment_approval',
                    'updated_at' => now(),
                ]);

            $this->logBookingAdjustmentActivity(
                $adjustmentId,
                (int) $booking->id,
                'provider',
                $providerId,
                $action,
                trim((string) ($data['provider_note'] ?? '')) ?: null,
                [
                    'reference_code' => $booking->reference_code,
                    'original_price' => $originalPrice,
                    'proposed_total' => $proposedTotal,
                    'reason_codes' => $reasonCodes,
                ]
            );

            $this->insertCustomerNotification(
                (int) $booking->customer_id,
                (string) $booking->reference_code,
                'Adjustment request for ref ' . $booking->reference_code
                    . '. Booked scope: ' . ($originalOptionSummary ?: $booking->service_name)
                    . '. Actual onsite scope: ' . ($correctedOptionSummary ?: $booking->service_name)
                    . '. Original total: PHP ' . number_format($originalPrice, 2)
                    . '. Added amount: PHP ' . number_format($additionalFee, 2)
                    . '. New total: PHP ' . number_format($proposedTotal, 2)
                    . (!empty($reasonCodes)
                        ? '. Reason: ' . collect($reasonCodes)
                            ->map(fn ($code) => $this->adjustmentReasonLabel((string) $code))
                            ->implode(', ')
                        : '')
                    . '. Please review the update and choose whether to accept it, keep the original booking, or cancel it.',
                'booking_adjustment'
            );

            DB::commit();

            return redirect()
                ->route('provider.bookings.show', $booking->reference_code)
                ->with('success', 'Mismatch reported. The customer was asked to review the adjustment.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->withErrors([
                    'general' => 'Unable to submit the mismatch report right now. Please try again.',
            ])->withInput();
        }
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

        $locationPayload = [
            'provider_id' => $providerId,
            'latitude' => $payload['latitude'],
            'longitude' => $payload['longitude'],
            'formatted_address' => $formattedAddress,
            'is_tracking' => true,
            'tracked_at' => now(),
            'stopped_at' => null,
        ];

        if ($this->providerLocationTimestampsAvailable()) {
            $locationPayload['updated_at'] = now();
            $locationPayload['created_at'] = now();
        }

        DB::table('booking_provider_locations')->updateOrInsert(
            ['booking_id' => $booking->id],
            $locationPayload
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

        $this->stopTrackingForBooking((int) $booking->id);

        return response()->json([
            'ok' => true,
        ]);
    }

    public function updateStatus(Request $request, string $reference)
    {
        $providerId = $this->providerId();

        $data = $request->validate([
            'status' => 'required|string|in:confirmed,in_progress,paid,completed,cancelled',
            'cancellation_reason' => 'nullable|string|max:600',
        ]);

        $booking = DB::table('bookings')
            ->where('provider_id', $providerId)
            ->where('reference_code', $reference)
            ->select(
                'id',
                'provider_id',
                'customer_id',
                'reference_code',
                'booking_date',
                'time_start',
                'time_end',
                'status',
                Schema::hasColumn('bookings', 'adjustment_status')
                    ? 'adjustment_status'
                    : DB::raw('NULL as adjustment_status')
            )
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
            ])->withInput();
        }

        $reason = trim((string) ($data['cancellation_reason'] ?? ''));

        if ($next === 'cancelled' && $reason === '') {
            return back()->withErrors([
                'cancellation_reason' => 'Please provide a reason before cancelling the booking.',
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($booking, $next, $providerId, $reason) {
                $update = ['status' => $next];
                $adjustment = $this->bookingAdjustmentByBookingId((int) $booking->id);

                if (Schema::hasColumn('bookings', 'updated_at')) {
                    $update['updated_at'] = now();
                }

                if ($next === 'cancelled') {
                    if (Schema::hasColumn('bookings', 'cancellation_reason')) {
                        $update['cancellation_reason'] = $reason;
                    }

                    if (Schema::hasColumn('bookings', 'cancelled_by_role')) {
                        $update['cancelled_by_role'] = 'provider';
                    }

                    if (
                        Schema::hasColumn('bookings', 'adjustment_status') &&
                        $adjustment &&
                        ($adjustment->status ?? '') === 'pending_adjustment_approval'
                    ) {
                        $update['adjustment_status'] = 'adjustment_rejected';
                    }
                }

                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update($update);

                if ($adjustment && ($adjustment->status ?? '') === 'pending_adjustment_approval' && $next === 'cancelled') {
                    DB::table('booking_adjustments')
                        ->where('id', $adjustment->id)
                        ->update([
                            'status' => 'adjustment_rejected',
                            'customer_response_note' => 'Provider cancelled the booking.',
                            'resolved_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $this->logBookingAdjustmentActivity(
                        (int) $adjustment->id,
                        (int) $booking->id,
                        'provider',
                        $providerId,
                        'cancelled_booking',
                        'Provider cancelled the booking after an adjustment request.',
                        [
                            'reference_code' => $booking->reference_code,
                            'cancellation_reason' => $reason,
                        ]
                    );
                }

                // Close live tracking once the booking leaves its active tracking states.
                if (!$this->bookingCanTrack($next)) {
                    $this->stopTrackingForBooking((int) $booking->id);
                }

                if ($next === 'cancelled') {
                    $booking->cancellation_reason = $reason;
                    $booking->cancelled_by_role = 'provider';
                    $this->restoreAvailabilitySlot($booking);
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
                    'cancelled'   => 'Provider cancelled booking ' . $booking->reference_code . '.' . ($reason !== '' ? ' Cancellation reason: ' . $reason : ''),
                    'confirmed'   => 'Your booking has been confirmed.',
                    default       => 'Your booking status has been updated to ' . $statusLabel . '.',
                };

                $this->insertCustomerNotification(
                    (int) $booking->customer_id,
                    (string) $booking->reference_code,
                    $message,
                    $next === 'completed' ? 'review' : ($next === 'cancelled' ? 'booking_cancelled' : 'booking_status')
                );
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['status' => 'Unable to update this booking right now. Please try again.'])
                ->withInput();
        }

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
