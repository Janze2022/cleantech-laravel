<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminBookingController extends Controller
{
    public function index()
    {
        $currentStatuses = ['confirmed', 'in_progress', 'paid'];
        $historyStatuses = ['completed', 'cancelled'];
        $hasCustomersTable = Schema::hasTable('customers');
        $hasProvidersTable = Schema::hasTable('service_providers');
        $hasServicesTable = Schema::hasTable('services');
        $hasServiceOptionsTable = Schema::hasTable('service_options');

        /*
        |--------------------------------------------------------------------------
        | Detect customer name columns safely
        |--------------------------------------------------------------------------
        */
        if ($hasCustomersTable && Schema::hasColumn('customers', 'name')) {
            $customerNameSql = "customers.name";
        } elseif ($hasCustomersTable && Schema::hasColumn('customers', 'full_name')) {
            $customerNameSql = "customers.full_name";
        } else {
            $customerNameSql = "CONCAT('Customer ID: ', bookings.customer_id)";
        }

        /*
        |--------------------------------------------------------------------------
        | Detect provider name columns safely
        |--------------------------------------------------------------------------
        */
        if (
            $hasProvidersTable &&
            Schema::hasColumn('service_providers', 'first_name') &&
            Schema::hasColumn('service_providers', 'last_name')
        ) {
            $providerNameSql = "
                TRIM(CONCAT(
                    COALESCE(service_providers.first_name, ''),
                    ' ',
                    COALESCE(service_providers.last_name, '')
                ))
            ";
        } elseif ($hasProvidersTable && Schema::hasColumn('service_providers', 'name')) {
            $providerNameSql = "service_providers.name";
        } elseif ($hasProvidersTable && Schema::hasColumn('service_providers', 'full_name')) {
            $providerNameSql = "service_providers.full_name";
        } else {
            $providerNameSql = "CONCAT('Provider ID: ', bookings.provider_id)";
        }

        /*
        |--------------------------------------------------------------------------
        | Detect service name column safely
        |--------------------------------------------------------------------------
        */
        if ($hasServicesTable) {
            if (Schema::hasColumn('services', 'name')) {
                $serviceNameSql = "services.name";
            } elseif (Schema::hasColumn('services', 'service_name')) {
                $serviceNameSql = "services.service_name";
            } elseif (Schema::hasColumn('services', 'title')) {
                $serviceNameSql = "services.title";
            } else {
                $serviceNameSql = "CONCAT('Service #', bookings.service_id)";
            }
        } else {
            $serviceNameSql = "CONCAT('Service #', bookings.service_id)";
        }

        /*
        |--------------------------------------------------------------------------
        | Detect service option name column safely
        |--------------------------------------------------------------------------
        */
        if ($hasServiceOptionsTable) {
            if (Schema::hasColumn('service_options', 'name')) {
                $serviceOptionNameSql = "service_options.name";
            } elseif (Schema::hasColumn('service_options', 'option_name')) {
                $serviceOptionNameSql = "service_options.option_name";
            } elseif (Schema::hasColumn('service_options', 'label')) {
                $serviceOptionNameSql = "service_options.label";
            } elseif (Schema::hasColumn('service_options', 'title')) {
                $serviceOptionNameSql = "service_options.title";
            } else {
                $serviceOptionNameSql = "CONCAT('Option #', bookings.service_option_id)";
            }
        } else {
            $serviceOptionNameSql = "CONCAT('Option #', bookings.service_option_id)";
        }

        $customerPhoneSelect = $hasCustomersTable && Schema::hasColumn('customers', 'phone')
            ? DB::raw('customers.phone as customer_phone')
            : DB::raw('NULL as customer_phone');

        $providerPhoneSelect = $hasProvidersTable && Schema::hasColumn('service_providers', 'phone')
            ? DB::raw('service_providers.phone as provider_phone')
            : DB::raw('NULL as provider_phone');

        $missingBookingColumnSelects = [];

        foreach ([
            'house_type',
            'contact_phone',
            'address',
            'adjustment_status',
            'cancellation_reason',
            'cancelled_by_role',
        ] as $column) {
            if (!Schema::hasColumn('bookings', $column)) {
                $missingBookingColumnSelects[] = DB::raw("NULL as {$column}");
            }
        }

        $baseQuery = DB::table('bookings')
            ->when($hasCustomersTable, function ($query) {
                $query->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id');
            })
            ->when($hasProvidersTable, function ($query) {
                $query->leftJoin('service_providers', 'bookings.provider_id', '=', 'service_providers.id');
            })
            ->when($hasServicesTable, function ($query) {
                $query->leftJoin('services', 'bookings.service_id', '=', 'services.id');
            })
            ->when($hasServiceOptionsTable, function ($query) {
                $query->leftJoin('service_options', 'bookings.service_option_id', '=', 'service_options.id');
            })
            ->select(array_merge(
                ['bookings.*'],
                $missingBookingColumnSelects,
                [
                    DB::raw("$customerNameSql as customer_name"),
                    $customerPhoneSelect,
                    DB::raw("$providerNameSql as provider_name"),
                    $providerPhoneSelect,
                    DB::raw("$serviceNameSql as service_name"),
                    DB::raw("$serviceOptionNameSql as service_option_name"),
                ]
            ))
            ->orderByDesc('bookings.created_at');

        $currentBookings = (clone $baseQuery)
            ->whereIn('bookings.status', $currentStatuses)
            ->get();

        $bookingHistory = (clone $baseQuery)
            ->whereIn('bookings.status', $historyStatuses)
            ->get();

        $adjustmentSummary = $this->attachAdjustmentData($currentBookings, $bookingHistory);

        /*
        |--------------------------------------------------------------------------
        | Customers dropdown
        |--------------------------------------------------------------------------
        */
        $customers = collect();

        if ($hasCustomersTable) {
            if (Schema::hasColumn('customers', 'name')) {
                $customerDisplaySql = "customers.name";
            } elseif (Schema::hasColumn('customers', 'full_name')) {
                $customerDisplaySql = "customers.full_name";
            } else {
                $customerDisplaySql = "CONCAT('Customer #', customers.id)";
            }

            $customerPhoneDropdownSelect = Schema::hasColumn('customers', 'phone')
                ? DB::raw('customers.phone as phone')
                : DB::raw('NULL as phone');

            $customers = DB::table('customers')
                ->select(
                    'customers.id',
                    DB::raw("$customerDisplaySql as display_name"),
                    $customerPhoneDropdownSelect
                )
                ->orderBy('customers.id')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Providers dropdown
        |--------------------------------------------------------------------------
        */
        $providers = collect();

        if ($hasProvidersTable) {
            if (
                Schema::hasColumn('service_providers', 'first_name') &&
                Schema::hasColumn('service_providers', 'last_name')
            ) {
                $providerDisplaySql = "
                    TRIM(CONCAT(
                        COALESCE(service_providers.first_name, ''),
                        ' ',
                        COALESCE(service_providers.last_name, '')
                    ))
                ";
            } elseif (Schema::hasColumn('service_providers', 'name')) {
                $providerDisplaySql = "service_providers.name";
            } elseif (Schema::hasColumn('service_providers', 'full_name')) {
                $providerDisplaySql = "service_providers.full_name";
            } else {
                $providerDisplaySql = "CONCAT('Provider #', service_providers.id)";
            }

            $providerPhoneDropdownSelect = Schema::hasColumn('service_providers', 'phone')
                ? DB::raw('service_providers.phone as phone')
                : DB::raw('NULL as phone');

            $providers = DB::table('service_providers')
                ->select(
                    'service_providers.id',
                    DB::raw("$providerDisplaySql as display_name"),
                    $providerPhoneDropdownSelect
                )
                ->orderBy('service_providers.id')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Services dropdown
        |--------------------------------------------------------------------------
        */
        $services = collect();

        if ($hasServicesTable) {
            if (Schema::hasColumn('services', 'name')) {
                $serviceDisplaySql = "services.name";
            } elseif (Schema::hasColumn('services', 'service_name')) {
                $serviceDisplaySql = "services.service_name";
            } elseif (Schema::hasColumn('services', 'title')) {
                $serviceDisplaySql = "services.title";
            } else {
                $serviceDisplaySql = "CONCAT('Service #', services.id)";
            }

            $servicesQuery = DB::table('services')
                ->select(
                    'services.id',
                    DB::raw("$serviceDisplaySql as display_name")
                );

            if (Schema::hasColumn('services', 'is_active')) {
                $servicesQuery->where('services.is_active', 1);
            }

            $services = $servicesQuery
                ->orderBy('display_name')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Service options dropdown
        |--------------------------------------------------------------------------
        */
        $serviceOptions = collect();

        if ($hasServiceOptionsTable) {
            if (Schema::hasColumn('service_options', 'name')) {
                $serviceOptionDisplaySql = "service_options.name";
            } elseif (Schema::hasColumn('service_options', 'option_name')) {
                $serviceOptionDisplaySql = "service_options.option_name";
            } elseif (Schema::hasColumn('service_options', 'label')) {
                $serviceOptionDisplaySql = "service_options.label";
            } elseif (Schema::hasColumn('service_options', 'title')) {
                $serviceOptionDisplaySql = "service_options.title";
            } else {
                $serviceOptionDisplaySql = "CONCAT('Option #', service_options.id)";
            }

            $serviceOptionsQuery = DB::table('service_options')
                ->select(
                    'service_options.id',
                    'service_options.service_id',
                    DB::raw("$serviceOptionDisplaySql as display_name")
                );

            if (Schema::hasColumn('service_options', 'is_active')) {
                $serviceOptionsQuery->where('service_options.is_active', 1);
            }

            $serviceOptions = $serviceOptionsQuery
                ->orderBy('display_name')
                ->get();
        }

        return view('admin.bookings', [
            'currentBookings' => $currentBookings,
            'bookingHistory'  => $bookingHistory,
            'customers'       => $customers,
            'providers'       => $providers,
            'services'        => $services,
            'serviceOptions'  => $serviceOptions,
            'adjustmentSummary' => $adjustmentSummary,
        ]);
    }

    protected function attachAdjustmentData($currentBookings, $bookingHistory): array
    {
        $summary = [
            'pending' => 0,
            'accepted' => 0,
            'rejected' => 0,
        ];

        if (
            !Schema::hasTable('booking_adjustments') ||
            !Schema::hasColumn('booking_adjustments', 'booking_id') ||
            !$currentBookings instanceof \Illuminate\Support\Collection ||
            !$bookingHistory instanceof \Illuminate\Support\Collection
        ) {
            return $summary;
        }

        $bookingIds = $currentBookings
            ->concat($bookingHistory)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($bookingIds->isEmpty()) {
            return $summary;
        }

        $adjustmentsQuery = DB::table('booking_adjustments')
            ->whereIn('booking_id', $bookingIds->all())
            ->select([
                $this->tableColumnOrNull('booking_adjustments', 'id'),
                $this->tableColumnOrNull('booking_adjustments', 'booking_id'),
                $this->tableColumnOrNull('booking_adjustments', 'reason_payload'),
                $this->tableColumnOrNull('booking_adjustments', 'status'),
                $this->tableColumnOrNull('booking_adjustments', 'adjustment_status'),
                $this->tableColumnOrNull('booking_adjustments', 'original_service_name'),
                $this->tableColumnOrNull('booking_adjustments', 'original_option_summary'),
                $this->tableColumnOrNull('booking_adjustments', 'original_price'),
                $this->tableColumnOrNull('booking_adjustments', 'proposed_service_name'),
                $this->tableColumnOrNull('booking_adjustments', 'proposed_scope_summary'),
                $this->tableColumnOrNull('booking_adjustments', 'additional_fee'),
                $this->tableColumnOrNull('booking_adjustments', 'proposed_total'),
                $this->tableColumnOrNull('booking_adjustments', 'price_increase_percent'),
                $this->tableColumnOrNull('booking_adjustments', 'other_reason'),
                $this->tableColumnOrNull('booking_adjustments', 'provider_note'),
                $this->tableColumnOrNull('booking_adjustments', 'customer_response_note'),
                $this->tableColumnOrNull('booking_adjustments', 'evidence_path'),
                $this->tableColumnOrNull('booking_adjustments', 'evidence_name'),
                $this->tableColumnOrNull('booking_adjustments', 'resolved_at'),
                $this->tableColumnOrNull('booking_adjustments', 'created_at'),
                $this->tableColumnOrNull('booking_adjustments', 'updated_at'),
            ]);

        if ($this->tableHasColumn('booking_adjustments', 'updated_at')) {
            $adjustmentsQuery->orderByDesc('booking_adjustments.updated_at');
        } elseif ($this->tableHasColumn('booking_adjustments', 'created_at')) {
            $adjustmentsQuery->orderByDesc('booking_adjustments.created_at');
        }

        $adjustments = $adjustmentsQuery
            ->get()
            ->keyBy('booking_id');

        if ($adjustments->isEmpty()) {
            return $summary;
        }

        $logsByAdjustment = collect();

        if (
            Schema::hasTable('booking_adjustment_logs') &&
            Schema::hasColumn('booking_adjustment_logs', 'booking_adjustment_id')
        ) {
            $adjustmentIds = $adjustments->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($adjustmentIds->isNotEmpty()) {
                $logsQuery = DB::table('booking_adjustment_logs')
                    ->whereIn('booking_adjustment_id', $adjustmentIds->all())
                    ->select([
                        $this->tableColumnOrNull('booking_adjustment_logs', 'booking_adjustment_id'),
                        $this->tableColumnOrNull('booking_adjustment_logs', 'created_at'),
                        $this->tableColumnOrNull('booking_adjustment_logs', 'payload'),
                        $this->tableColumnOrNull('booking_adjustment_logs', 'actor_role'),
                        $this->tableColumnOrNull('booking_adjustment_logs', 'action'),
                        $this->tableColumnOrNull('booking_adjustment_logs', 'note'),
                    ]);

                if ($this->tableHasColumn('booking_adjustment_logs', 'created_at')) {
                    $logsQuery->orderByDesc('booking_adjustment_logs.created_at');
                }

                $logsByAdjustment = $logsQuery
                    ->get()
                    ->groupBy('booking_adjustment_id')
                    ->map(function ($logs) {
                        return $logs->take(6)->map(function ($log) {
                            $payload = json_decode((string) ($log->payload ?? '[]'), true);
                            $payload = is_array($payload) ? $payload : [];

                            return [
                                'actor' => $this->adjustmentActorLabel((string) ($log->actor_role ?? '')),
                                'action' => $this->adjustmentActionLabel((string) ($log->action ?? '')),
                                'detail' => $this->adjustmentLogDetail((string) ($log->action ?? ''), $payload, (string) ($log->note ?? '')),
                                'created_at' => !empty($log->created_at)
                                    ? Carbon::parse($log->created_at)->format('M d, Y h:i A')
                                    : null,
                            ];
                        })->values()->all();
                    });
            }
        }

        foreach ([$currentBookings, $bookingHistory] as $collection) {
            $collection->transform(function ($booking) use ($adjustments, $logsByAdjustment, &$summary) {
                $adjustment = $adjustments->get((int) ($booking->id ?? 0));

                if (!$adjustment) {
                    $booking->adjustment_details = null;
                    $booking->adjustment_status_label = null;
                    $booking->adjustment_reason_text = null;

                    return $booking;
                }

                $formatted = $this->formatAdjustmentForAdmin(
                    $adjustment,
                    $logsByAdjustment->get((int) $adjustment->id, [])
                );

                $booking->adjustment_status = $formatted['status_key'];
                $booking->adjustment_status_label = $formatted['status_label'];
                $booking->adjustment_reason_text = implode(', ', $formatted['reason_labels']);
                $booking->adjustment_details = (object) $formatted;

                switch ($formatted['status_bucket']) {
                    case 'pending':
                        $summary['pending']++;
                        break;
                    case 'accepted':
                        $summary['accepted']++;
                        break;
                    case 'rejected':
                        $summary['rejected']++;
                        break;
                }

                return $booking;
            });
        }

        return $summary;
    }

    protected function tableHasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    protected function tableColumnOrNull(string $table, string $column, ?string $alias = null)
    {
        $alias = $alias ?: $column;

        if ($this->tableHasColumn($table, $column)) {
            return DB::raw("{$table}.{$column} as {$alias}");
        }

        return DB::raw("NULL as {$alias}");
    }

    protected function formatAdjustmentForAdmin(object $adjustment, array $logs = []): array
    {
        $reasonCodes = collect(json_decode((string) ($adjustment->reason_payload ?? '[]'), true))
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->values()
            ->all();

        $reasonLabels = collect($reasonCodes)
            ->map(fn ($code) => $this->adjustmentReasonLabel((string) $code))
            ->values()
            ->all();

        $statusKey = $this->normalizeAdjustmentStatusKey(
            (string) ($adjustment->status ?? $adjustment->adjustment_status ?? '')
        );

        return [
            'status_key' => $statusKey,
            'status_label' => $this->adjustmentStatusLabel($statusKey),
            'status_bucket' => $this->adjustmentStatusBucket($statusKey),
            'original_service_name' => trim((string) ($adjustment->original_service_name ?? '')) ?: 'Original booking',
            'original_option_summary' => trim((string) ($adjustment->original_option_summary ?? '')) ?: 'Original scope',
            'original_price' => (float) ($adjustment->original_price ?? 0),
            'original_price_display' => number_format((float) ($adjustment->original_price ?? 0), 2),
            'proposed_service_name' => trim((string) ($adjustment->proposed_service_name ?? '')) ?: 'Updated booking',
            'proposed_scope_summary' => trim((string) ($adjustment->proposed_scope_summary ?? '')) ?: 'Updated scope',
            'additional_fee' => (float) ($adjustment->additional_fee ?? 0),
            'additional_fee_display' => number_format((float) ($adjustment->additional_fee ?? 0), 2),
            'proposed_total' => (float) ($adjustment->proposed_total ?? 0),
            'proposed_total_display' => number_format((float) ($adjustment->proposed_total ?? 0), 2),
            'difference_display' => number_format(
                (float) (($adjustment->proposed_total ?? 0) - ($adjustment->original_price ?? 0)),
                2
            ),
            'price_increase_percent_display' => number_format((float) ($adjustment->price_increase_percent ?? 0), 1),
            'reason_labels' => $reasonLabels,
            'other_reason' => trim((string) ($adjustment->other_reason ?? '')) ?: null,
            'provider_note' => trim((string) ($adjustment->provider_note ?? '')) ?: null,
            'customer_response_note' => trim((string) ($adjustment->customer_response_note ?? '')) ?: null,
            'evidence_url' => !empty($adjustment->evidence_path)
                ? route('booking.adjustments.attachment', ['filename' => basename((string) $adjustment->evidence_path)])
                : null,
            'evidence_name' => trim((string) ($adjustment->evidence_name ?? '')) ?: null,
            'resolved_at_label' => !empty($adjustment->resolved_at)
                ? Carbon::parse($adjustment->resolved_at)->format('M d, Y h:i A')
                : null,
            'submitted_at_label' => !empty($adjustment->created_at)
                ? Carbon::parse($adjustment->created_at)->format('M d, Y h:i A')
                : null,
            'logs' => $logs,
        ];
    }

    protected function normalizeAdjustmentStatusKey(string $status): string
    {
        $status = strtolower(trim($status));
        $status = str_replace(['-', ' '], '_', $status);

        return match ($status) {
            'accepted', 'adjustment_accepted' => 'adjustment_accepted',
            'rejected', 'adjustment_rejected' => 'adjustment_rejected',
            'pending_adjustment_approval' => 'pending_adjustment_approval',
            default => $status,
        };
    }

    protected function adjustmentStatusBucket(string $status): ?string
    {
        return match ($status) {
            'pending_adjustment_approval' => 'pending',
            'adjustment_accepted' => 'accepted',
            'adjustment_rejected' => 'rejected',
            default => null,
        };
    }

    protected function adjustmentStatusLabel(string $status): string
    {
        return match ($status) {
            'pending_adjustment_approval' => 'Pending Adjustment Approval',
            'adjustment_accepted' => 'Adjustment Accepted',
            'adjustment_rejected' => 'Adjustment Rejected',
            default => ucwords(str_replace('_', ' ', $status ?: 'Adjustment')),
        };
    }

    protected function adjustmentReasonLabel(string $code): string
    {
        return match (strtolower(trim($code))) {
            'larger_area' => 'Larger area than declared',
            'additional_rooms' => 'Additional rooms or sections',
            'heavy_soiling' => 'Heavily soiled or deep cleaning required',
            'other' => 'Other onsite issue',
            default => ucwords(str_replace('_', ' ', $code)),
        };
    }

    protected function adjustmentActorLabel(string $actorRole): string
    {
        return match (strtolower(trim($actorRole))) {
            'provider' => 'Provider',
            'customer' => 'Customer',
            'admin' => 'Admin',
            default => 'System',
        };
    }

    protected function adjustmentActionLabel(string $action): string
    {
        return match (strtolower(trim($action))) {
            'created' => 'Adjustment sent',
            'updated' => 'Adjustment updated',
            'accepted' => 'Adjustment accepted',
            'revision_requested' => 'Customer asked for a review',
            'rejected' => 'Adjustment rejected',
            'cancelled_booking' => 'Booking cancelled',
            default => ucwords(str_replace('_', ' ', trim($action))),
        };
    }

    protected function adjustmentLogDetail(string $action, array $payload, string $note = ''): ?string
    {
        $action = strtolower(trim($action));
        $note = trim($note);

        return match ($action) {
            'created', 'updated' => $this->firstFilledText([
                $this->priceChangeSummary($payload, 'original_price', 'proposed_total'),
                $note,
            ]),
            'accepted' => $this->firstFilledText([
                isset($payload['new_price'])
                    ? 'Customer approved PHP ' . number_format((float) $payload['new_price'], 2) . '.'
                    : null,
                $this->priceChangeSummary($payload, 'old_price', 'new_price'),
                $note,
            ]),
            'revision_requested' => $this->firstFilledText([
                isset($payload['requested_total'])
                    ? 'Customer asked the provider to review PHP ' . number_format((float) $payload['requested_total'], 2) . ' again before deciding.'
                    : null,
                isset($payload['kept_price'])
                    ? 'Customer is still holding the original total of PHP ' . number_format((float) $payload['kept_price'], 2) . '.'
                    : null,
                $note,
            ]),
            'rejected' => $this->firstFilledText([
                isset($payload['kept_price']) && isset($payload['rejected_total'])
                    ? 'Customer kept PHP ' . number_format((float) $payload['kept_price'], 2)
                        . ' and rejected PHP ' . number_format((float) $payload['rejected_total'], 2) . '.'
                    : null,
                $note,
            ]),
            'cancelled_booking' => $this->firstFilledText([
                !empty($payload['cancellation_reason'])
                    ? 'Reason: ' . trim((string) $payload['cancellation_reason'])
                    : null,
                $note,
            ]),
            default => $note !== '' ? $note : null,
        };
    }

    protected function priceChangeSummary(array $payload, string $fromKey, string $toKey): ?string
    {
        if (!isset($payload[$fromKey], $payload[$toKey])) {
            return null;
        }

        return 'PHP '
            . number_format((float) $payload[$fromKey], 2)
            . ' -> PHP '
            . number_format((float) $payload[$toKey], 2);
    }

    protected function firstFilledText(array $values): ?string
    {
        foreach ($values as $value) {
            $value = trim((string) ($value ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    public function store(Request $request)
    {
        $validated = $this->validateBooking($request);

        DB::table('bookings')->insert([
            'reference_code'    => $validated['reference_code'] ?? null,
            'customer_id'       => $validated['customer_id'],
            'provider_id'       => $validated['provider_id'],
            'service_id'        => $validated['service_id'],
            'service_option_id' => $validated['service_option_id'],
            'contact_phone'     => $validated['contact_phone'] ?? null,
            'address'           => $validated['address'] ?? null,
            'house_type'        => $validated['house_type'] ?? null,
            'booking_date'      => $validated['booking_date'],
            'time_start'        => $validated['time_start'],
            'time_end'          => $validated['time_end'],
            'price'             => $validated['price'] ?? 0,
            'status'            => $validated['status'],
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $this->logAdminAction('Created booking');

        return redirect()->route('admin.bookings')->with('success', 'Booking created successfully.');
    }

    public function update(Request $request, $id)
    {
        $booking = DB::table('bookings')->where('id', $id)->first();

        if (!$booking) {
            return redirect()->route('admin.bookings')->withErrors(['Booking not found.']);
        }

        $validated = $this->validateBooking($request, $id);

        DB::table('bookings')
            ->where('id', $id)
            ->update([
                'reference_code'    => $validated['reference_code'] ?? null,
                'customer_id'       => $validated['customer_id'],
                'provider_id'       => $validated['provider_id'],
                'service_id'        => $validated['service_id'],
                'service_option_id' => $validated['service_option_id'],
                'contact_phone'     => $validated['contact_phone'] ?? null,
                'address'           => $validated['address'] ?? null,
                'house_type'        => $validated['house_type'] ?? null,
                'booking_date'      => $validated['booking_date'],
                'time_start'        => $validated['time_start'],
                'time_end'          => $validated['time_end'],
                'price'             => $validated['price'] ?? 0,
                'status'            => $validated['status'],
                'updated_at'        => now(),
            ]);

        $this->logAdminAction("Updated booking #{$id}");

        return redirect()->route('admin.bookings')->with('success', 'Booking updated successfully.');
    }

    public function destroy($id)
    {
        $exists = DB::table('bookings')->where('id', $id)->exists();

        if (!$exists) {
            return redirect()->route('admin.bookings')->withErrors(['Booking not found.']);
        }

        DB::table('bookings')->where('id', $id)->delete();

        $this->logAdminAction("Deleted booking #{$id}");

        return redirect()->route('admin.bookings')->with('success', 'Booking deleted successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'in_progress', 'paid', 'completed', 'cancelled'])],
            'cancellation_reason' => ['nullable', 'string', 'max:600'],
        ]);

        $booking = DB::table('bookings')
            ->where('id', $id)
            ->first([
                'id',
                'reference_code',
                'customer_id',
                'provider_id',
                Schema::hasColumn('bookings', 'cancellation_reason')
                    ? 'cancellation_reason'
                    : DB::raw('NULL as cancellation_reason'),
                Schema::hasColumn('bookings', 'cancelled_by_role')
                    ? 'cancelled_by_role'
                    : DB::raw('NULL as cancelled_by_role'),
            ]);

        if (!$booking) {
            return redirect()->route('admin.bookings')->withErrors(['Booking not found.']);
        }

        $reason = trim((string) $request->input('cancellation_reason', ''));

        if ($request->status === 'cancelled' && $reason === '') {
            return back()->withErrors([
                'cancellation_reason' => 'Please provide a reason before cancelling the booking.',
            ]);
        }

        $update = [
            'status' => $request->status,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('bookings', 'cancellation_reason')) {
            $update['cancellation_reason'] = $request->status === 'cancelled' ? $reason : null;
        }

        if (Schema::hasColumn('bookings', 'cancelled_by_role')) {
            $update['cancelled_by_role'] = $request->status === 'cancelled' ? 'admin' : null;
        }

        DB::table('bookings')
            ->where('id', $id)
            ->update($update);

        $booking->status = $request->status;
        $booking->cancellation_reason = $request->status === 'cancelled' ? $reason : null;
        $booking->cancelled_by_role = $request->status === 'cancelled' ? 'admin' : null;

        if ($request->status === 'cancelled') {
            $this->notifyCustomerAboutAdminCancellation($booking);
            $this->notifyProviderAboutAdminCancellation($booking);
        }

        $this->logAdminAction(
            $request->status === 'cancelled' && $reason !== ''
                ? "Updated booking #{$id} status to {$request->status} ({$reason})"
                : "Updated booking #{$id} status to {$request->status}"
        );

        return back()->with('success', 'Booking status updated.');
    }

    protected function validateBooking(Request $request, $id = null): array
    {
        return $request->validate([
            'reference_code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('bookings', 'reference_code')->ignore($id),
            ],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'provider_id' => ['required', 'integer', 'exists:service_providers,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'service_option_id' => ['required', 'integer', 'exists:service_options,id'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'house_type' => ['nullable', Rule::in(['small', 'medium', 'big', 'second_floor', 'full_clean'])],
            'booking_date' => ['required', 'date'],
            'time_start' => ['required'],
            'time_end' => ['required', 'after:time_start'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['confirmed', 'in_progress', 'paid', 'completed', 'cancelled'])],
        ]);
    }

    protected function notifyCustomerAboutAdminCancellation(object $booking): void
    {
        if (
            !Schema::hasTable('notifications') ||
            !Schema::hasColumns('notifications', ['user_id', 'reference_code', 'message', 'is_read'])
        ) {
            return;
        }

        $customerId = (int) ($booking->customer_id ?? 0);
        if (!$customerId) {
            return;
        }

        $reason = trim((string) ($booking->cancellation_reason ?? ''));
        $message = 'Admin cancelled booking ' . $booking->reference_code . '.';

        if ($reason !== '') {
            $message .= ' Cancellation reason: ' . $reason;
        }

        $payload = [
            'user_id' => $customerId,
            'reference_code' => (string) ($booking->reference_code ?? ''),
            'message' => $message,
            'is_read' => 0,
        ];

        if (Schema::hasColumn('notifications', 'type')) {
            $payload['type'] = 'booking_cancelled';
        }

        if (Schema::hasColumn('notifications', 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn('notifications', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('notifications')->insert($payload);
    }

    protected function notifyProviderAboutAdminCancellation(object $booking): void
    {
        if (
            !Schema::hasTable('provider_notifications') ||
            !Schema::hasColumns('provider_notifications', ['provider_id', 'message', 'is_read'])
        ) {
            return;
        }

        $providerId = (int) ($booking->provider_id ?? 0);
        if (!$providerId) {
            return;
        }

        $reason = trim((string) ($booking->cancellation_reason ?? ''));
        $message = 'Admin cancelled booking ' . $booking->reference_code . '.';

        if ($reason !== '') {
            $message .= ' Cancellation reason: ' . $reason;
        }

        $payload = [
            'provider_id' => $providerId,
            'message' => $message,
            'is_read' => 0,
        ];

        if (Schema::hasColumn('provider_notifications', 'type')) {
            $payload['type'] = 'booking_cancelled';
        }

        if (Schema::hasColumn('provider_notifications', 'reference_code')) {
            $payload['reference_code'] = (string) ($booking->reference_code ?? '');
        }

        if (Schema::hasColumn('provider_notifications', 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn('provider_notifications', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('provider_notifications')->insert($payload);
    }

    protected function logAdminAction(string $action): void
    {
        if (!Schema::hasTable('admin_logs')) {
            return;
        }

        $data = [
            'action' => $action,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('admin_logs', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if (Schema::hasColumn('admin_logs', 'admin_id') && session()->has('admin_id')) {
            $data['admin_id'] = session('admin_id');
        }

        DB::table('admin_logs')->insert($data);
    }
}
