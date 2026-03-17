<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProviderNotificationController extends Controller
{
    private function redirectRelative(string $routeName, array $parameters = []): RedirectResponse
    {
        $response = new RedirectResponse(route($routeName, $parameters, false));

        if (app()->bound('session.store')) {
            $response->setSession(app('session.store'));
        }

        return $response;
    }

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
            return $this->redirectRelative('provider.dashboard');
        }

        $notif = DB::table('provider_notifications')
            ->where('id', $id)
            ->where('provider_id', $providerId)
            ->first();

        if (!$notif) {
            return $this->redirectRelative('provider.dashboard')
                ->with('error', 'Notification not found.');
        }

        $update = ['is_read' => 1];

        if (Schema::hasColumn('provider_notifications', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('provider_notifications')
            ->where('id', $id)
            ->update($update);

        $reference = trim((string) ($notif->reference_code ?? ''));

        if ($reference !== '' && Schema::hasTable('bookings')) {
            $bookingExists = DB::table('bookings')
                ->where('provider_id', $providerId)
                ->where('reference_code', $reference)
                ->exists();

            if ($bookingExists) {
                return $this->redirectRelative('provider.bookings.show', [
                    'reference_code' => $reference,
                ]);
            }
        }

        return $this->redirectRelative('provider.bookings')
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
