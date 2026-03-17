<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        if (
            !Schema::hasTable('provider_notifications') ||
            !Schema::hasColumns('provider_notifications', ['provider_id', 'is_read'])
        ) {
            return redirect()->route('provider.dashboard');
        }

        $notif = DB::table('provider_notifications')
            ->where('id', $id)
            ->where('provider_id', $providerId)
            ->first();

        if (!$notif) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Notification not found.');
        }

        $update = ['is_read' => 1];

        if (Schema::hasColumn('provider_notifications', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('provider_notifications')
            ->where('id', $id)
            ->update($update);

        return redirect()->route('provider.bookings')
            ->with('success', 'Notification opened.');
    }

    public function readAll(Request $request)
    {
        $providerId = $this->providerId();

        if (
            !Schema::hasTable('provider_notifications') ||
            !Schema::hasColumns('provider_notifications', ['provider_id', 'is_read'])
        ) {
            return back();
        }

        $update = ['is_read' => 1];

        if (Schema::hasColumn('provider_notifications', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('provider_notifications')
            ->where('provider_id', $providerId)
            ->where('is_read', 0)
            ->update($update);

        return back();
    }

    public function clear(Request $request)
    {
        $providerId = $this->providerId();

        if (!Schema::hasTable('provider_notifications') || !Schema::hasColumn('provider_notifications', 'provider_id')) {
            return back();
        }

        DB::table('provider_notifications')
            ->where('provider_id', $providerId)
            ->delete();

        return back();
    }
}
