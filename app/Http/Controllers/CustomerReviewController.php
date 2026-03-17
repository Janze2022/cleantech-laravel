<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerReviewController extends Controller
{
    public function index()
    {
        $customerId = session('user_id');

        $areasSub = $this->bookingAreasSubquery();

        $reviews = DB::table('bookings as b')
            ->join('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->leftJoin('reviews as r', 'r.booking_id', '=', 'b.id')
            ->join('service_providers as p', 'p.id', '=', 'b.provider_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->where('b.customer_id', $customerId)
            ->where('b.status', 'completed')
            ->select(
                'b.id as booking_id',
                'b.reference_code',
                'b.booking_date',
                'b.price',
                's.name as service_name',
                DB::raw("COALESCE(areas.areas_label, o.label) as option_name"),
                DB::raw("TRIM(CONCAT(COALESCE(p.first_name,''), ' ', COALESCE(p.last_name,''))) as provider"),
                'r.rating',
                'r.comment'
            )
            ->orderByDesc('b.booking_date')
            ->orderByDesc('b.created_at')
            ->get();

        return view('customer.reviews.index', compact('reviews'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        DB::table('reviews')->insert([
            'booking_id' => $request->booking_id,
            'customer_id' => session('user_id'),
            'provider_id' => DB::table('bookings')
                ->where('id', $request->booking_id)
                ->value('provider_id'),
            'rating' => $request->rating,
            'comment' => $request->comment,
            'created_at' => now()
        ]);

        return back()->with('success', 'Review submitted successfully.');
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
}
