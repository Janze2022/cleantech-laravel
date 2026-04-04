<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        /*
        |--------------------------------------------------------------------------
        | Detect customer name columns safely
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('customers', 'name')) {
            $customerNameSql = "customers.name";
        } elseif (Schema::hasColumn('customers', 'full_name')) {
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
        } elseif (Schema::hasColumn('service_providers', 'name')) {
            $providerNameSql = "service_providers.name";
        } elseif (Schema::hasColumn('service_providers', 'full_name')) {
            $providerNameSql = "service_providers.full_name";
        } else {
            $providerNameSql = "CONCAT('Provider ID: ', bookings.provider_id)";
        }

        /*
        |--------------------------------------------------------------------------
        | Detect service name column safely
        |--------------------------------------------------------------------------
        */
        $hasServicesTable = Schema::hasTable('services');

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
        $hasServiceOptionsTable = Schema::hasTable('service_options');

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

        $baseQuery = DB::table('bookings')
            ->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
            ->leftJoin('service_providers', 'bookings.provider_id', '=', 'service_providers.id')
            ->when($hasServicesTable, function ($query) {
                $query->leftJoin('services', 'bookings.service_id', '=', 'services.id');
            })
            ->when($hasServiceOptionsTable, function ($query) {
                $query->leftJoin('service_options', 'bookings.service_option_id', '=', 'service_options.id');
            })
            ->select(
                'bookings.*',
                DB::raw("$customerNameSql as customer_name"),
                DB::raw("customers.phone as customer_phone"),
                DB::raw("$providerNameSql as provider_name"),
                DB::raw("service_providers.phone as provider_phone"),
                DB::raw("$serviceNameSql as service_name"),
                DB::raw("$serviceOptionNameSql as service_option_name")
            )
            ->orderByDesc('bookings.created_at');

        $currentBookings = (clone $baseQuery)
            ->whereIn('bookings.status', $currentStatuses)
            ->get();

        $bookingHistory = (clone $baseQuery)
            ->whereIn('bookings.status', $historyStatuses)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Customers dropdown
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('customers', 'name')) {
            $customerDisplaySql = "customers.name";
        } elseif (Schema::hasColumn('customers', 'full_name')) {
            $customerDisplaySql = "customers.full_name";
        } else {
            $customerDisplaySql = "CONCAT('Customer #', customers.id)";
        }

        $customers = DB::table('customers')
            ->select(
                'customers.id',
                DB::raw("$customerDisplaySql as display_name"),
                DB::raw("customers.phone as phone")
            )
            ->orderBy('customers.id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Providers dropdown
        |--------------------------------------------------------------------------
        */
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

        $providers = DB::table('service_providers')
            ->select(
                'service_providers.id',
                DB::raw("$providerDisplaySql as display_name"),
                DB::raw("service_providers.phone as phone")
            )
            ->orderBy('service_providers.id')
            ->get();

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
        ]);
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
