<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReviewController extends Controller
{
    public function index()
    {
        $customerId = session('user_id');

        $reviews = DB::table('bookings as b')
            ->leftJoin('reviews as r', 'r.booking_id', '=', 'b.id')
            ->join('service_providers as p', 'p.id', '=', 'b.provider_id')
            ->where('b.customer_id', $customerId)
            ->where('b.status', 'completed')
            ->select(
                'b.id as booking_id',
                'b.reference_code',
                DB::raw("CONCAT(p.first_name,' ',p.last_name) as provider"),
                'r.rating',
                'r.comment'
            )
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
}
