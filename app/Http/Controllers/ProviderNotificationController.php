<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderNotificationController extends Controller
{
    private function providerId(): int
    {
        $providerId = (int) session('provider_id');

        if (!$providerId) {
            abort(403, 'Provider session missing.');
        }

        return $providerId;
    }

    public function open($id)
    {
        $providerId = $this->providerId();

        $notif = DB::table('provider_notifications')
            ->where('id', $id)
            ->where('provider_id', $providerId)
            ->first();

        if (!$notif) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Notification not found.');
        }

        DB::table('provider_notifications')
            ->where('id', $id)
            ->update([
                'is_read' => 1,
                'updated_at' => now(),
            ]);

        return redirect()->route('provider.bookings')
            ->with('success', 'Notification opened.');
    }

    public function readAll(Request $request)
    {
        $providerId = $this->providerId();

        DB::table('provider_notifications')
            ->where('provider_id', $providerId)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
                'updated_at' => now(),
            ]);

        return back();
    }

    public function clear(Request $request)
    {
        $providerId = $this->providerId();

        DB::table('provider_notifications')
            ->where('provider_id', $providerId)
            ->delete();

        return back();
    }
}