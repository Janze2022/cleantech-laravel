<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerBookingController extends Controller
{
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

    public function create(int $provider)
    {
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
            ->whereDate('pa.date', '>=', now()->toDateString())
            ->orderBy('pa.date')
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

        return view('customer.book_service', compact(
            'providerData',
            'services',
            'optionsByService',
            'availability'
        ));
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

            $fullAddress = trim(implode(', ', array_filter([
                $data['address'] ?? null,
                $data['barangay'] ?? null,
                $data['city'] ?? null,
                $data['province'] ?? null,
                $data['region'] ?? null,
            ])));

            $reference = 'CT-' . strtoupper(uniqid());

            $bookingId = DB::table('bookings')->insertGetId([
                'reference_code'       => $reference,
                'customer_id'          => session('user_id'),
                'provider_id'          => $data['provider_id'],
                'service_id'           => $serviceId,
                'service_option_id'    => $primaryOptionId,
                'contact_phone'        => $data['phone'],
                'address'              => $fullAddress ?: $data['address'],
                'booking_date'         => $slotDate,
                'requested_start_time' => $preferred,
                'time_start'           => $slotStart,
                'time_end'             => $slotEnd,
                'price'                => $totalPrice,
                'status'               => 'confirmed',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // Notify provider about new booking
            if (Schema::hasTable('provider_notifications')) {
                DB::table('provider_notifications')->insert([
                    'provider_id'    => $data['provider_id'],
                    'type'           => 'new_booking',
                    'message'        => 'You received a new booking. Ref: ' . $reference,
                    'reference_code' => $reference,
                    'is_read'        => 0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
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
            ->join('service_options as o', 'o.id', '=', 'b.service_option_id')
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
            ->join('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->join('service_providers as p', 'p.id', '=', 'b.provider_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->where('b.customer_id', session('user_id'))
            ->orderByDesc('b.created_at')
            ->select(
                'b.reference_code',
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
            ->join('service_options as o', 'o.id', '=', 'b.service_option_id')
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

        return view('customer.bookings.show', compact('booking'));
    }

    private function bookingAreasSubquery()
    {
        if (!Schema::hasTable('booking_service_options')) {
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
}