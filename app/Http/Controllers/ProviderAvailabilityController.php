<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderAvailabilityController extends Controller
{
    public function index()
    {
        $providerId = session('provider_id');

        if (!$providerId) {
            return redirect()->route('provider.login');
        }

        $provider = DB::table('service_providers')
            ->where('id', $providerId)
            ->first();

        if (!$provider) {
            session()->forget('provider_id');
            return redirect()->route('provider.login');
        }

        if ($provider->status !== 'Approved') {
            return redirect()->route('provider.pending');
        }

        $availability = DB::table('provider_availability')
            ->where('provider_id', $providerId)
            ->orderBy('date')
            ->orderBy('time_start')
            ->get();

        return view('provider.availability', compact('availability'));
    }

    public function store(Request $request)
    {
        $providerId = session('provider_id');

        if (!$providerId) {
            return redirect()->route('provider.login');
        }

        $request->validate([
            'date'       => 'required|date|after_or_equal:today',
            'time_start' => 'required',
            'time_end'   => 'required',
        ]);

        // 🚫 DISALLOW OVERNIGHT TIME
        if (strtotime($request->time_end) <= strtotime($request->time_start)) {
            return back()->withErrors([
                'time_end' => 'End time must be later than start time (same-day only).',
            ])->withInput();
        }

        // 🚫 PREVENT OVERLAPPING ACTIVE SLOTS
        $overlap = DB::table('provider_availability')
            ->where('provider_id', $providerId)
            ->where('date', $request->date)
            ->where('status', 'active')
            ->where('time_start', '<', $request->time_end)
            ->where('time_end', '>', $request->time_start)
            ->exists();

        if ($overlap) {
            return back()->withErrors([
                'time_start' => 'This time overlaps with an existing active availability.',
            ])->withInput();
        }

        DB::table('provider_availability')->insert([
            'provider_id' => $providerId,
            'date'        => $request->date,
            'time_start'  => $request->time_start,
            'time_end'    => $request->time_end,
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Availability added successfully.');
    }

    /**
     * Toggle availability (active ↔ inactive)
     */
    public function toggle($id)
    {
        $providerId = session('provider_id');

        if (!$providerId) {
            return redirect()->route('provider.login');
        }

        $slot = DB::table('provider_availability')
            ->where('id', $id)
            ->where('provider_id', $providerId)
            ->first();

        if (!$slot) {
            abort(404);
        }

        $newStatus = $slot->status === 'active' ? 'inactive' : 'active';

        DB::table('provider_availability')
            ->where('id', $id)
            ->update([
                'status'     => $newStatus,
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Availability ' . ($newStatus === 'active' ? 'activated.' : 'ended.')
        );
    }

    /**
     * 🗑 DELETE availability
     */
    public function destroy($id)
    {
        $providerId = session('provider_id');

        if (!$providerId) {
            return redirect()->route('provider.login');
        }

        $slot = DB::table('provider_availability')
            ->where('id', $id)
            ->where('provider_id', $providerId)
            ->first();

        if (!$slot) {
            abort(404);
        }

        DB::table('provider_availability')
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'Availability deleted successfully.');
    }
}
