<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicesController extends Controller
{
    public function index(Request $request)
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
        $todayDateString = $today->toDateString();

        $services = DB::table('services')
            ->where('is_active', 1)
            ->get();

        $providers = DB::table('service_providers')
            ->join('provider_availability', 'service_providers.id', '=', 'provider_availability.provider_id')
            ->where('service_providers.status', 'Approved')
            ->where('service_providers.is_verified', 1)
            ->where('provider_availability.status', 'active')
            ->whereDate('provider_availability.date', $selectedDateString)
            ->select(
                'service_providers.id',
                'service_providers.first_name',
                'service_providers.last_name',
                'service_providers.city',
                'service_providers.province',
                'service_providers.profile_image',
                'provider_availability.date as availability_date'
            )
            ->distinct()
            ->get();

        return view('customer.services', compact(
            'services',
            'providers',
            'selectedDateString',
            'selectedDateLabel',
            'todayDateString'
        ));
    }

    public function provider(int $id)
    {
        $provider = DB::table('service_providers')
            ->where('id', $id)
            ->where('status', 'Approved')
            ->where('is_verified', 1)
            ->first();

        abort_if(!$provider, 404);

        $ratingSummary = DB::table('reviews')
            ->where('provider_id', $id)
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

        $reviews = DB::table('reviews as r')
            ->join('customers as c', 'c.id', '=', 'r.customer_id')
            ->where('r.provider_id', $id)
            ->whereNotNull('r.rating')
            ->where('r.rating', '>', 0)
            ->orderByDesc('r.created_at')
            ->select(
                'r.rating',
                'r.comment',
                'r.created_at',
                'c.profile_image as customer_profile_image',
                DB::raw("COALESCE(NULLIF(TRIM(c.name),''), NULLIF(TRIM(c.email),''), 'Customer') as customer_name")
            )
            ->get();

        return view('customer.provider_profile', compact('provider', 'ratingSummary', 'reviews'));
    }
}
