<?php

namespace App\Http\Controllers;

use App\Services\GeoapifyService;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CustomerBookingController extends Controller
{
    private const CUSTOMER_CANCELLABLE_STATUSES = [
        'pending',
        'accepted',
        'confirmed',
        'scheduled',
    ];

    private const CUSTOMER_TRACKING_ACTIVE_STATUSES = [
        'in_progress',
        'ongoing',
        'active',
    ];

    /**
     * PROVIDER PROFILE (Customer side)
     * URL example: /customer/providers/1
     *
     * Blade view: resources/views/customer/provider/profile.blade.php
     */
    public function providerProfile(int $providerId)
    {
        $provider = DB::table('service_providers')
            ->where('id', $providerId)
            ->select(
                'id',
                'first_name',
                'last_name',
                'city',
                'province',
                'region',
                'barangay',
                'address',
                'email',
                'phone',
                'status',
                'profile_image'
            )
            ->first();

        abort_if(!$provider, 404);

        $reviews = DB::table('reviews as r')
            ->join('customers as c', 'c.id', '=', 'r.customer_id')
            ->where('r.provider_id', $providerId)
            ->whereNotNull('r.rating')
            ->where('r.rating', '>', 0)
            ->orderByDesc('r.created_at')
            ->select(
                'r.rating',
                'r.comment',
                'r.created_at',
                DB::raw("COALESCE(NULLIF(TRIM(c.name), ''), NULLIF(TRIM(c.email), ''), 'Customer') as customer_name")
            )
            ->get();

        $ratingSummary = DB::table('reviews')
            ->where('provider_id', $providerId)
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->selectRaw('AVG(rating) as avg, COUNT(*) as count')
            ->first();

        if (!$ratingSummary) {
            $ratingSummary = (object) [
                'avg' => 0,
                'count' => 0,
            ];
        }

        return view('customer.provider.profile', compact('provider', 'ratingSummary', 'reviews'));
    }

    public function create(Request $request, int $provider)
    {
        $timezone = config('app.timezone') ?: 'Asia/Manila';
        $today = Carbon::now($timezone)->startOfDay();
        $requestedDate = trim((string) $request->query('date', ''));

        try {
            $selectedDate = $requestedDate !== ''
                ? Carbon::createFromFormat('Y-m-d', $requestedDate, $timezone)->startOfDay()
                : $today->copy();
        } catch (\Throwable $exception) {
            $selectedDate = $today->copy();
        }

        if ($selectedDate->lt($today)) {
            $selectedDate = $today->copy();
        }

        $selectedDateString = $selectedDate->toDateString();
        $selectedDateLabel = $selectedDate->format('F d, Y');

        $providerData = DB::table('service_providers')
            ->where('id', $provider)
            ->where('status', 'Approved')
            ->select(
                'id',
                'first_name',
                'last_name',
                'city',
                'province',
                'profile_image',
                'phone'
            )
            ->first();

        abort_if(!$providerData, 404);

        $services = DB::table('services')
            ->where('is_active', 1)
            ->select('id', 'name', 'base_price')
            ->orderBy('name')
            ->get();

        $optionsByService = DB::table('service_options')
            ->select('id', 'service_id', 'label', 'price_addition')
            ->orderBy('label')
            ->get()
            ->groupBy('service_id');

        $availability = DB::table('provider_availability as pa')
            ->join('service_providers as sp', 'sp.id', '=', 'pa.provider_id')
            ->where('pa.provider_id', $provider)
            ->where('pa.status', 'active')
            ->whereDate('pa.date', $selectedDateString)
            ->orderBy('pa.time_start')
            ->select(
                'pa.id',
                'pa.provider_id',
                'pa.date',
                'pa.time_start',
                'pa.time_end',
                'sp.first_name',
                'sp.last_name',
                'sp.profile_image'
            )
            ->get();

        $specificAreaServiceId = $this->getSpecificAreaServiceId();

        return view('customer.book_service', compact(
            'providerData',
            'services',
            'optionsByService',
            'availability',
            'specificAreaServiceId',
            'selectedDateString',
            'selectedDateLabel'
        ));
    }

    public function autocompleteAddress(Request $request, GeoapifyService $geoapify): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:160'],
        ]);

        if (!$geoapify->configured()) {
            return response()->json([
                'results' => [],
                'message' => 'Map search is not configured right now.',
            ], 503);
        }

        try {
            $results = $geoapify->autocomplete((string) $request->query('q'), 6, [
                'filter' => 'countrycode:ph',
                'bias' => 'proximity:125.5436,8.9475',
            ]);

            return response()->json([
                'results' => $results,
            ]);
        } catch (RequestException $exception) {
            report($exception);

            $status = $exception->response?->status();

            return response()->json([
                'results' => [],
                'message' => in_array($status, [401, 403], true)
                    ? 'Geoapify rejected the API key. Please check GEOAPIFY_API_KEY in Laravel Cloud.'
                    : 'Unable to search addresses right now.',
            ], 502);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'results' => [],
                'message' => 'Unable to search addresses right now.',
            ], 502);
        }
    }

    public function reverseGeocode(Request $request, GeoapifyService $geoapify): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        if (!$geoapify->configured()) {
            return response()->json([
                'message' => 'Map reverse geocoding is not configured right now.',
            ], 503);
        }

        try {
            $result = $geoapify->reverseGeocode(
                (float) $request->query('lat'),
                (float) $request->query('lng')
            );

            if (!$result) {
                return response()->json([
                    'message' => 'No readable address was found for that pin.',
                ], 404);
            }

            return response()->json([
                'result' => $result,
            ]);
        } catch (RequestException $exception) {
            report($exception);

            $status = $exception->response?->status();

            return response()->json([
                'message' => in_array($status, [401, 403], true)
                    ? 'Geoapify rejected the API key. Please check GEOAPIFY_API_KEY in Laravel Cloud.'
                    : 'Unable to reverse geocode that location right now.',
            ], 502);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to reverse geocode that location right now.',
            ], 502);
        }
    }

    public function store(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect()
                ->route('customer.login')
                ->withErrors(['general' => 'Session expired. Please login again.']);
        }

        $specificAreaServiceId = $this->getSpecificAreaServiceId();

        $data = $request->validate([
            'provider_id'          => ['required', 'integer'],
            'service_id'           => ['required', 'integer'],
            'slot_id'              => ['required', 'integer'],
            'preferred_start_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],

            'phone'    => ['required', 'regex:/^09\d{9}$/'],
            'address'  => ['required', 'string', 'max:255'],
            'region'   => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'city'     => ['nullable', 'string', 'max:100'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'formatted_address' => ['nullable', 'string', 'max:500'],
            'customer_latitude' => ['required', 'numeric', 'between:-90,90'],
            'customer_longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $serviceId = (int) $data['service_id'];

        if ($specificAreaServiceId && $serviceId === $specificAreaServiceId) {
            $request->validate([
                'service_option_ids'   => ['required', 'array', 'min:1'],
                'service_option_ids.*' => ['integer', 'distinct'],
            ]);
        } else {
            $request->validate([
                'service_option_id' => ['required', 'integer'],
            ]);
        }

        $data['preferred_start_time'] = $this->normalizeTime($data['preferred_start_time']);

        $providerExists = DB::table('service_providers')
            ->where('id', $data['provider_id'])
            ->where('status', 'Approved')
            ->exists();

        if (!$providerExists) {
            return back()
                ->withErrors(['provider_id' => 'Selected provider is invalid.'])
                ->withInput();
        }

        $serviceRow = DB::table('services')
            ->where('id', $serviceId)
            ->where('is_active', 1)
            ->select('id', 'base_price', 'name')
            ->first();

        if (!$serviceRow) {
            return back()
                ->withErrors(['service_id' => 'Invalid service selection.'])
                ->withInput();
        }

        $selectedOptionIds = [];
        $primaryOptionId = null;
        $optionTotal = 0;

        if ($specificAreaServiceId && $serviceId === $specificAreaServiceId) {
            $selectedOptionIds = collect($request->input('service_option_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            $selectedOptions = DB::table('service_options')
                ->where('service_id', $serviceId)
                ->whereIn('id', $selectedOptionIds)
                ->select('id', 'label', 'price_addition')
                ->get();

            if (count($selectedOptionIds) === 0 || $selectedOptions->count() !== count($selectedOptionIds)) {
                return back()
                    ->withErrors(['service_option_ids' => 'Please select valid areas.'])
                    ->withInput();
            }

            $optionTotal = (float) $selectedOptions->sum('price_addition');
            $primaryOptionId = (int) $selectedOptionIds[0];
        } else {
            $primaryOptionId = (int) $request->input('service_option_id');

            $priceRow = DB::table('service_options')
                ->where('id', $primaryOptionId)
                ->where('service_id', $serviceId)
                ->select('id', 'price_addition')
                ->first();

            if (!$priceRow) {
                return back()
                    ->withErrors(['service_option_id' => 'Invalid service selection.'])
                    ->withInput();
            }

            $optionTotal = (float) $priceRow->price_addition;
            $selectedOptionIds = [$primaryOptionId];
        }

        $totalPrice = (float) $serviceRow->base_price + $optionTotal;

        try {
            DB::beginTransaction();

            $slot = DB::table('provider_availability')
                ->where('id', $data['slot_id'])
                ->where('provider_id', $data['provider_id'])
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$slot) {
                DB::rollBack();

                return back()
                    ->withErrors(['slot_id' => 'Selected schedule is no longer available.'])
                    ->withInput();
            }

            $slotDate  = $slot->date;
            $slotStart = $this->normalizeTime($slot->time_start);
            $slotEnd   = $this->normalizeTime($slot->time_end);
            $preferred = $data['preferred_start_time'];

            if (
                strtotime($preferred) < strtotime($slotStart) ||
                strtotime($preferred) >= strtotime($slotEnd)
            ) {
                DB::rollBack();

                return back()
                    ->withErrors([
                        'preferred_start_time' => 'Preferred start time must be within the provider availability.',
                    ])
                    ->withInput();
            }

            $manualAddress = trim((string) ($data['address'] ?? ''));
            $formattedAddress = trim((string) ($data['formatted_address'] ?? ''));

            $fullAddress = $this->buildBookingAddress(
                $manualAddress,
                $formattedAddress,
                [
                    $data['barangay'] ?? null,
                    $data['city'] ?? null,
                    $data['province'] ?? null,
                    $data['region'] ?? null,
                ]
            );

            $reference = 'CT-' . strtoupper(uniqid());

            $bookingInsert = [
                'reference_code'       => $reference,
                'customer_id'          => session('user_id'),
                'provider_id'          => $data['provider_id'],
                'service_id'           => $serviceId,
                'service_option_id'    => $primaryOptionId,
                'contact_phone'        => $data['phone'],
                'address'              => $fullAddress ?: $manualAddress,
                'booking_date'         => $slotDate,
                'requested_start_time' => $preferred,
                'time_start'           => $slotStart,
                'time_end'             => $slotEnd,
                'price'                => $totalPrice,
                'status'               => 'confirmed',
                'created_at'           => now(),
                'updated_at'           => now(),
            ];

            if (Schema::hasColumn('bookings', 'formatted_address')) {
                $bookingInsert['formatted_address'] = $formattedAddress !== '' ? $formattedAddress : null;
            }

            if (Schema::hasColumn('bookings', 'customer_latitude')) {
                $bookingInsert['customer_latitude'] = $data['customer_latitude'] ?? null;
            }

            if (Schema::hasColumn('bookings', 'customer_longitude')) {
                $bookingInsert['customer_longitude'] = $data['customer_longitude'] ?? null;
            }

            $bookingId = DB::table('bookings')->insertGetId($bookingInsert);

            // Notify provider about new booking
            if (
                Schema::hasTable('provider_notifications') &&
                Schema::hasColumns('provider_notifications', ['provider_id', 'message', 'is_read'])
            ) {
                $notification = [
                    'provider_id' => $data['provider_id'],
                    'message' => 'You received a new booking. Ref: ' . $reference,
                    'is_read' => 0,
                ];

                if (Schema::hasColumn('provider_notifications', 'type')) {
                    $notification['type'] = 'new_booking';
                }

                if (Schema::hasColumn('provider_notifications', 'reference_code')) {
                    $notification['reference_code'] = $reference;
                }

                if (Schema::hasColumn('provider_notifications', 'created_at')) {
                    $notification['created_at'] = now();
                }

                if (Schema::hasColumn('provider_notifications', 'updated_at')) {
                    $notification['updated_at'] = now();
                }

                DB::table('provider_notifications')->insert($notification);
            }

            if (Schema::hasTable('booking_service_options')) {
                DB::table('booking_service_options')
                    ->where('booking_id', $bookingId)
                    ->delete();

                $rows = [];
                foreach ($selectedOptionIds as $optionId) {
                    $rows[] = [
                        'booking_id'        => $bookingId,
                        'service_option_id' => $optionId,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }

                if (!empty($rows)) {
                    DB::table('booking_service_options')->insert($rows);
                }
            }

            DB::table('provider_availability')
                ->where('id', $slot->id)
                ->update([
                    'status'     => 'inactive',
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()->route('customer.book.confirmed', ['reference' => $reference]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors(['general' => 'Something went wrong. Try again.'])
                ->withInput();
        }
    }

    public function confirmed(string $reference)
    {
        $customerId = session('user_id');

        $areasSub = $this->bookingAreasSubquery();

        $booking = DB::table('bookings as b')
            ->join('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->join('service_providers as p', 'p.id', '=', 'b.provider_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->where('b.customer_id', $customerId)
            ->where('b.reference_code', $reference)
            ->select(
                'b.reference_code',
                'b.booking_date',
                'b.requested_start_time',
                'b.time_start',
                'b.time_end',
                'b.price',
                'b.status',
                'b.address',
                'b.contact_phone',
                'b.created_at',
                's.name as service_name',
                DB::raw("COALESCE(areas.areas_label, o.label) as option_name"),
                DB::raw("TRIM(CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, ''))) as provider_name"),
                'p.phone as provider_phone',
                'p.city as provider_city',
                'p.province as provider_province'
            )
            ->first();

        abort_if(!$booking, 404);

        return view('customer.book_confirmed', compact('booking'));
    }

    public function index()
    {
        $areasSub = $this->bookingAreasSubquery();

        $bookings = DB::table('bookings as b')
            ->join('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->join('service_providers as p', 'p.id', '=', 'b.provider_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->where('b.customer_id', session('user_id'))
            ->orderByDesc('b.created_at')
            ->select(
                'b.id',
                'b.reference_code',
                'b.provider_id',
                's.name as service',
                DB::raw("COALESCE(areas.areas_label, o.label) as `option`"),
                DB::raw("TRIM(CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, ''))) as provider_name"),
                'p.profile_image',
                'b.booking_date',
                'b.requested_start_time',
                'b.time_start',
                'b.time_end',
                'b.price',
                'b.status'
            )
            ->get();

        return view('customer.bookings.index', compact('bookings'));
    }

    public function show(string $reference)
    {
        $customerId = session('user_id');

        $areasSub = $this->bookingAreasSubquery();

        $booking = DB::table('bookings as b')
            ->join('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->join('service_providers as p', 'p.id', '=', 'b.provider_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->where('b.customer_id', $customerId)
            ->where('b.reference_code', $reference)
            ->select(
                'b.id',
                'b.reference_code',
                'b.provider_id',
                'b.booking_date',
                'b.requested_start_time',
                'b.time_start',
                'b.time_end',
                'b.price',
                'b.status',
                Schema::hasColumn('bookings', 'cancellation_reason')
                    ? 'b.cancellation_reason'
                    : DB::raw('NULL as cancellation_reason'),
                Schema::hasColumn('bookings', 'cancelled_by_role')
                    ? 'b.cancelled_by_role'
                    : DB::raw('NULL as cancelled_by_role'),
                Schema::hasColumn('bookings', 'adjustment_status')
                    ? 'b.adjustment_status'
                    : DB::raw('NULL as adjustment_status'),
                'b.address',
                'b.contact_phone',
                'b.created_at',
                's.name as service_name',
                DB::raw("COALESCE(areas.areas_label, o.label) as option_name"),
                DB::raw("TRIM(CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, ''))) as provider_name"),
                'p.phone as provider_phone',
                'p.city as provider_city',
                'p.province as provider_province',
                $this->selectBookingLocationColumn('customer_latitude'),
                $this->selectBookingLocationColumn('customer_longitude'),
                $this->selectBookingLocationColumn('formatted_address')
            )
            ->first();

        abort_if(!$booking, 404);

        $adjustmentRecord = $this->bookingAdjustmentByBookingId((int) $booking->id);
        $adjustment = $this->formatBookingAdjustment($adjustmentRecord);
        $adjustmentLogs = $adjustmentRecord
            ? $this->formattedAdjustmentLogs((int) $adjustmentRecord->id)
            : collect();

        return view('customer.bookings.show', compact('booking', 'adjustment', 'adjustmentLogs'));
    }

    public function tracking(string $reference, GeoapifyService $geoapify): JsonResponse
    {
        $customerId = (int) session('user_id');

        $booking = DB::table('bookings')
            ->where('customer_id', $customerId)
            ->where('reference_code', $reference)
            ->first([
                'id',
                'reference_code',
                'status',
                'provider_id',
                $this->rawBookingLocationColumn('customer_latitude'),
                $this->rawBookingLocationColumn('customer_longitude'),
                $this->rawBookingLocationColumn('formatted_address'),
                'address',
            ]);

        abort_if(!$booking, 404);

        $status = $this->normalizeStatus((string) ($booking->status ?? ''));
        $trackingReady = in_array($status, self::CUSTOMER_TRACKING_ACTIVE_STATUSES, true);

        $providerLocation = null;
        if ($trackingReady && $this->providerLocationTableAvailable()) {
            $providerLocation = DB::table('booking_provider_locations')
                ->where('booking_id', $booking->id)
                ->select(
                    'latitude',
                    'longitude',
                    'formatted_address',
                    'is_tracking',
                    'tracked_at',
                    'updated_at'
                )
                ->first();
        }

        $route = null;
        if (
            $trackingReady &&
            $geoapify->configured() &&
            !empty($booking->customer_latitude) &&
            !empty($booking->customer_longitude) &&
            !empty($providerLocation?->latitude) &&
            !empty($providerLocation?->longitude)
        ) {
            try {
                $route = $geoapify->route(
                    (float) $providerLocation->latitude,
                    (float) $providerLocation->longitude,
                    (float) $booking->customer_latitude,
                    (float) $booking->customer_longitude
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return response()->json([
            'booking' => [
                'reference_code' => $booking->reference_code,
                'status' => $booking->status,
                'tracking_ready' => $trackingReady,
                'address' => $booking->address,
                'formatted_address' => $booking->formatted_address ?? null,
                'customer_latitude' => $booking->customer_latitude ?? null,
                'customer_longitude' => $booking->customer_longitude ?? null,
            ],
            'provider_location' => $providerLocation ? [
                'latitude' => $providerLocation->latitude,
                'longitude' => $providerLocation->longitude,
                'formatted_address' => $providerLocation->formatted_address,
                'is_tracking' => (bool) $providerLocation->is_tracking,
                'tracked_at' => $providerLocation->tracked_at ?? $providerLocation->updated_at,
            ] : null,
            'route' => $route,
            'message' => $trackingReady
                ? null
                : 'Live tracking starts once the provider begins the booking.',
        ]);
    }

    public function cancel(Request $request, string $reference)
    {
        $customerId = (int) session('user_id');

        if (!$customerId) {
            return redirect()
                ->route('customer.login')
                ->withErrors(['general' => 'Session expired. Please login again.']);
        }

        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:600'],
        ]);

        try {
            DB::beginTransaction();

            $booking = DB::table('bookings')
                ->where('customer_id', $customerId)
                ->where('reference_code', $reference)
                ->lockForUpdate()
                ->first([
                    'id',
                    'reference_code',
                    'provider_id',
                    'booking_date',
                    'time_start',
                    'time_end',
                    'status',
                    Schema::hasColumn('bookings', 'adjustment_status')
                        ? 'adjustment_status'
                        : DB::raw('NULL as adjustment_status'),
                ]);

            if (!$booking) {
                DB::rollBack();
                abort(404);
            }

            $status = $this->normalizeStatus((string) ($booking->status ?? ''));

            if (!$this->customerCanCancelStatus($status)) {
                DB::rollBack();

                return back()->withErrors([
                    'general' => 'This booking can no longer be cancelled from the customer side once work is already in progress.',
                ]);
            }

            $reason = trim((string) $data['cancellation_reason']);

            $adjustment = $this->bookingAdjustmentByBookingId((int) $booking->id);

            DB::table('bookings')
                ->where('id', $booking->id)
                ->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $reason,
                    'cancelled_by_role' => 'customer',
                    'adjustment_status' => $adjustment && $adjustment->status === 'pending_adjustment_approval'
                        ? 'adjustment_rejected'
                        : ($booking->adjustment_status ?? null),
                    'updated_at' => now(),
                ]);

            if ($adjustment && $adjustment->status === 'pending_adjustment_approval') {
                DB::table('booking_adjustments')
                    ->where('id', $adjustment->id)
                    ->update([
                        'status' => 'adjustment_rejected',
                        'customer_response_note' => 'Customer cancelled the booking.',
                        'resolved_at' => now(),
                        'updated_at' => now(),
                    ]);

                $this->logBookingAdjustmentActivity(
                    (int) $adjustment->id,
                    (int) $booking->id,
                    'customer',
                    $customerId,
                    'cancelled_booking',
                    'Customer cancelled the booking after an adjustment request.',
                    [
                        'reference_code' => $booking->reference_code,
                        'cancellation_reason' => $reason,
                    ]
                );
            }

            $booking->cancellation_reason = $reason;
            $booking->cancelled_by_role = 'customer';
            $this->restoreAvailabilitySlot($booking);
            $this->notifyProviderAboutCancellation($booking);

            DB::commit();

            return redirect()
                ->route('customer.bookings.show', $booking->reference_code)
                ->with('success', 'Booking cancelled successfully.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->withErrors([
                'general' => 'Unable to cancel this booking right now. Please try again.',
            ]);
        }
    }

    public function respondToAdjustment(Request $request, string $reference)
    {
        $customerId = (int) session('user_id');

        if (!$customerId) {
            return redirect()
                ->route('customer.login')
                ->withErrors(['general' => 'Session expired. Please login again.']);
        }

        $data = $request->validate([
            'response' => ['required', 'string', 'in:accept,reject,reject_cancel'],
            'customer_response_note' => ['nullable', 'string', 'max:1000'],
            'cancellation_reason' => ['nullable', 'string', 'max:600'],
        ]);

        if (!$this->bookingAdjustmentTableAvailable()) {
            return back()->withErrors([
                'general' => 'Booking adjustments are not available right now.',
            ]);
        }

        try {
            DB::beginTransaction();

            $booking = DB::table('bookings')
                ->where('customer_id', $customerId)
                ->where('reference_code', $reference)
                ->lockForUpdate()
                ->first([
                    'id',
                    'reference_code',
                    'provider_id',
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
                    'general' => 'There is no pending adjustment to respond to.',
                ]);
            }

            $note = trim((string) ($data['customer_response_note'] ?? ''));
            $note = $note !== '' ? $note : null;
            $cancellationReason = trim((string) ($data['cancellation_reason'] ?? ''));

            if ($data['response'] === 'accept') {
                $proposedOptionIds = collect(json_decode((string) ($adjustment->proposed_option_ids_payload ?? '[]'), true))
                    ->map(fn ($value) => (int) $value)
                    ->filter(fn ($value) => $value > 0)
                    ->unique()
                    ->values()
                    ->all();

                if (empty($proposedOptionIds) && !empty($adjustment->proposed_service_option_id)) {
                    $proposedOptionIds = [(int) $adjustment->proposed_service_option_id];
                }

                $bookingUpdate = [
                    'price' => $adjustment->proposed_total,
                    'adjustment_status' => 'adjustment_accepted',
                    'updated_at' => now(),
                ];

                if (!empty($proposedOptionIds) && Schema::hasColumn('bookings', 'service_option_id')) {
                    $bookingUpdate['service_option_id'] = (int) $proposedOptionIds[0];
                }

                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update($bookingUpdate);

                if (
                    !empty($proposedOptionIds)
                    && Schema::hasTable('booking_service_options')
                    && Schema::hasColumns('booking_service_options', ['booking_id', 'service_option_id'])
                ) {
                    DB::table('booking_service_options')
                        ->where('booking_id', $booking->id)
                        ->delete();

                    $rows = collect($proposedOptionIds)
                        ->map(fn ($optionId) => [
                            'booking_id' => $booking->id,
                            'service_option_id' => (int) $optionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ])
                        ->values()
                        ->all();

                    if (!empty($rows)) {
                        DB::table('booking_service_options')->insert($rows);
                    }
                }

                DB::table('booking_adjustments')
                    ->where('id', $adjustment->id)
                    ->update([
                        'status' => 'adjustment_accepted',
                        'customer_response_note' => $note,
                        'resolved_at' => now(),
                        'updated_at' => now(),
                    ]);

                $this->logBookingAdjustmentActivity(
                    (int) $adjustment->id,
                    (int) $booking->id,
                    'customer',
                    $customerId,
                    'accepted',
                    $note,
                    [
                        'reference_code' => $booking->reference_code,
                        'old_price' => (float) ($booking->price ?? 0),
                        'new_price' => (float) ($adjustment->proposed_total ?? 0),
                        'applied_option_ids' => $proposedOptionIds,
                    ]
                );

                $this->notifyProviderAboutAdjustmentDecision(
                    $booking,
                    'accepted',
                    $note,
                    (float) ($adjustment->proposed_total ?? 0)
                );

                DB::commit();

                return redirect()
                    ->route('customer.bookings.show', $booking->reference_code)
                    ->with('success', 'Adjustment accepted. The booking total was updated.');
            }

            if ($data['response'] === 'reject_cancel') {
                if ($cancellationReason === '') {
                    DB::rollBack();

                    return back()->withErrors([
                        'cancellation_reason' => 'Please provide a reason before cancelling this booking.',
                    ])->withInput();
                }

                $bookingUpdate = [
                    'status' => 'cancelled',
                    'adjustment_status' => 'adjustment_rejected',
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('bookings', 'cancellation_reason')) {
                    $bookingUpdate['cancellation_reason'] = $cancellationReason;
                }

                if (Schema::hasColumn('bookings', 'cancelled_by_role')) {
                    $bookingUpdate['cancelled_by_role'] = 'customer';
                }

                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update($bookingUpdate);

                DB::table('booking_adjustments')
                    ->where('id', $adjustment->id)
                    ->update([
                        'status' => 'adjustment_rejected',
                        'customer_response_note' => $note ?: 'Customer rejected the adjustment and cancelled the booking.',
                        'resolved_at' => now(),
                        'updated_at' => now(),
                    ]);

                $this->logBookingAdjustmentActivity(
                    (int) $adjustment->id,
                    (int) $booking->id,
                    'customer',
                    $customerId,
                    'cancelled_booking',
                    $note,
                    [
                        'reference_code' => $booking->reference_code,
                        'cancellation_reason' => $cancellationReason,
                        'kept_price' => (float) ($booking->price ?? 0),
                        'rejected_total' => (float) ($adjustment->proposed_total ?? 0),
                    ]
                );

                $booking->cancellation_reason = $cancellationReason;
                $booking->cancelled_by_role = 'customer';

                if ($this->providerLocationTableAvailable()) {
                    $trackingUpdate = ['is_tracking' => 0];

                    if (Schema::hasColumn('booking_provider_locations', 'updated_at')) {
                        $trackingUpdate['updated_at'] = now();
                    }

                    if (Schema::hasColumn('booking_provider_locations', 'stopped_at')) {
                        $trackingUpdate['stopped_at'] = now();
                    }

                    DB::table('booking_provider_locations')
                        ->where('booking_id', $booking->id)
                        ->update($trackingUpdate);
                }

                $this->restoreAvailabilitySlot($booking);
                $this->notifyProviderAboutCancellation($booking);

                DB::commit();

                return redirect()
                    ->route('customer.bookings.show', $booking->reference_code)
                    ->with('success', 'Adjustment rejected and booking cancelled.');
            }

            DB::table('bookings')
                ->where('id', $booking->id)
                ->update([
                    'adjustment_status' => 'adjustment_rejected',
                    'updated_at' => now(),
                ]);

            DB::table('booking_adjustments')
                ->where('id', $adjustment->id)
                ->update([
                    'status' => 'adjustment_rejected',
                    'customer_response_note' => $note,
                    'resolved_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->logBookingAdjustmentActivity(
                (int) $adjustment->id,
                (int) $booking->id,
                'customer',
                $customerId,
                'rejected',
                $note,
                [
                    'reference_code' => $booking->reference_code,
                    'kept_price' => (float) ($booking->price ?? 0),
                    'rejected_total' => (float) ($adjustment->proposed_total ?? 0),
                ]
            );

            $this->notifyProviderAboutAdjustmentDecision(
                $booking,
                'rejected',
                $note,
                (float) ($adjustment->proposed_total ?? 0)
            );

            DB::commit();

            return redirect()
                ->route('customer.bookings.show', $booking->reference_code)
                ->with('success', 'Adjustment rejected. The booking keeps the original scope and price.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->withErrors([
                'general' => 'Unable to save your adjustment response right now. Please try again.',
            ]);
        }
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
            ->selectRaw("bso.booking_id, GROUP_CONCAT(so2.label ORDER BY so2.label SEPARATOR ', ') as areas_label")
            ->groupBy('bso.booking_id');
    }

    private function getSpecificAreaServiceId(): ?int
    {
        $id = DB::table('services')
            ->whereRaw('LOWER(name) = ?', ['specific area cleaning'])
            ->value('id');

        return $id ? (int) $id : null;
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

    private function buildBookingAddress(string $manualAddress, string $formattedAddress, array $fallbackParts = []): string
    {
        $manualAddress = trim($manualAddress);
        $formattedAddress = trim($formattedAddress);
        $fallbackAddress = trim(implode(', ', array_filter(array_map('trim', $fallbackParts))));

        if ($formattedAddress === '') {
            return trim(implode(', ', array_filter([$manualAddress, $fallbackAddress])));
        }

        if ($manualAddress === '') {
            return $formattedAddress;
        }

        if (stripos($formattedAddress, $manualAddress) !== false) {
            return $formattedAddress;
        }

        return trim($manualAddress . ', ' . $formattedAddress);
    }

    private function selectBookingLocationColumn(string $column)
    {
        return Schema::hasColumn('bookings', $column)
            ? DB::raw("b.{$column} as {$column}")
            : DB::raw("NULL as {$column}");
    }

    private function rawBookingLocationColumn(string $column)
    {
        return Schema::hasColumn('bookings', $column)
            ? $column
            : DB::raw("NULL as {$column}");
    }

    private function providerLocationTableAvailable(): bool
    {
        return Schema::hasTable('booking_provider_locations')
            && Schema::hasColumns('booking_provider_locations', [
                'booking_id',
                'latitude',
                'longitude',
                'formatted_address',
                'is_tracking',
            ]);
    }

    private function bookingAdjustmentTableAvailable(): bool
    {
        return Schema::hasTable('booking_adjustments')
            && Schema::hasColumns('booking_adjustments', [
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
        return Schema::hasTable('booking_adjustment_logs')
            && Schema::hasColumns('booking_adjustment_logs', [
                'booking_adjustment_id',
                'booking_id',
                'actor_role',
                'actor_id',
                'action',
                'payload',
            ]);
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

    private function formatBookingAdjustment(?object $adjustment): ?object
    {
        if (!$adjustment) {
            return null;
        }

        $adjustment->reason_codes = collect(json_decode((string) ($adjustment->reason_payload ?? '[]'), true))
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->values()
            ->all();

        $adjustment->reason_labels = collect($adjustment->reason_codes)
            ->map(fn ($code) => $this->adjustmentReasonLabel($code))
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
        if (!$this->bookingAdjustmentLogTableAvailable()) {
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
            'accepted' => 'Adjustment accepted',
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
            'accepted' => $this->firstFilled([
                isset($payload['new_price'])
                    ? 'Customer agreed to the updated total of PHP ' . number_format((float) $payload['new_price'], 2) . '.'
                    : null,
                $this->priceChangeSummary($payload, 'old_price', 'new_price'),
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

    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $stringValue = trim((string) $value);
            if ($stringValue !== '') {
                return $stringValue;
            }
        }

        return null;
    }

    private function adjustmentReasonLabel(string $code): string
    {
        return match (Str::lower(trim($code))) {
            'larger_area' => 'Larger area than declared',
            'additional_rooms' => 'Additional rooms or sections',
            'heavy_soiling' => 'Heavily soiled or deep cleaning required',
            'other' => 'Other onsite issue',
            default => ucwords(str_replace('_', ' ', $code)),
        };
    }

    private function normalizeStatus(string $status): string
    {
        $value = strtolower(trim($status));

        if (in_array($value, ['canceled', 'cancel'], true)) {
            return 'cancelled';
        }

        return $value;
    }

    private function customerCanCancelStatus(string $status): bool
    {
        return in_array($this->normalizeStatus($status), self::CUSTOMER_CANCELLABLE_STATUSES, true);
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
                $status = $this->normalizeStatus((string) ($row->status ?? ''));

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
            DB::table('provider_availability')
                ->where('id', $matchingSlot->id)
                ->update([
                    'status' => 'active',
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('provider_availability')->insert([
            'provider_id' => $providerId,
            'date' => $bookingDate,
            'time_start' => $slotStart,
            'time_end' => $slotEnd,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function notifyProviderAboutCancellation(object $booking): void
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
        $message = 'Customer cancelled booking ' . $booking->reference_code . '.';

        if ($reason !== '') {
            $message .= ' Cancellation reason: ' . $reason;
        }

        $notification = [
            'provider_id' => $providerId,
            'message' => $message,
            'is_read' => 0,
        ];

        if (Schema::hasColumn('provider_notifications', 'type')) {
            $notification['type'] = 'booking_cancelled';
        }

        if (Schema::hasColumn('provider_notifications', 'reference_code')) {
            $notification['reference_code'] = $booking->reference_code;
        }

        if (Schema::hasColumn('provider_notifications', 'created_at')) {
            $notification['created_at'] = now();
        }

        if (Schema::hasColumn('provider_notifications', 'updated_at')) {
            $notification['updated_at'] = now();
        }

        DB::table('provider_notifications')->insert($notification);
    }

    private function notifyProviderAboutAdjustmentDecision(
        object $booking,
        string $decision,
        ?string $note = null,
        ?float $proposedTotal = null
    ): void {
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

        $message = $decision === 'accepted'
            ? 'The customer accepted the booking adjustment for ref ' . $booking->reference_code . '.'
            : 'The customer rejected the booking adjustment for ref ' . $booking->reference_code . '.';

        if ($decision === 'accepted' && $proposedTotal !== null) {
            $message .= ' Total updated from PHP ' . number_format((float) ($booking->price ?? 0), 2)
                . ' to PHP ' . number_format($proposedTotal, 2) . '.';
        } elseif ($decision === 'rejected') {
            if ($proposedTotal !== null) {
                $message .= ' Requested total was PHP ' . number_format($proposedTotal, 2) . '.';
            }

            $message .= ' The booking stays on the original total of PHP ' . number_format((float) ($booking->price ?? 0), 2) . '.';
        }

        if ($note) {
            $message .= ' Note: ' . $note;
        }

        $notification = [
            'provider_id' => $providerId,
            'message' => $message,
            'is_read' => 0,
        ];

        if (Schema::hasColumn('provider_notifications', 'type')) {
            $notification['type'] = 'booking_adjustment';
        }

        if (Schema::hasColumn('provider_notifications', 'reference_code')) {
            $notification['reference_code'] = $booking->reference_code;
        }

        if (Schema::hasColumn('provider_notifications', 'created_at')) {
            $notification['created_at'] = now();
        }

        if (Schema::hasColumn('provider_notifications', 'updated_at')) {
            $notification['updated_at'] = now();
        }

        DB::table('provider_notifications')->insert($notification);
    }

    private function logBookingAdjustmentActivity(
        int $adjustmentId,
        int $bookingId,
        string $actorRole,
        int $actorId,
        string $action,
        ?string $note = null,
        array $payload = []
    ): void {
        if (!$this->bookingAdjustmentLogTableAvailable()) {
            return;
        }

        DB::table('booking_adjustment_logs')->insert([
            'booking_adjustment_id' => $adjustmentId,
            'booking_id' => $bookingId,
            'actor_role' => $actorRole,
            'actor_id' => $actorId,
            'action' => $action,
            'note' => $note,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
