<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerNotificationController extends Controller
{
    private function redirectRelative(string $routeName, array $parameters = []): RedirectResponse
    {
        $response = new RedirectResponse(route($routeName, $parameters, false));

        if (app()->bound('session.store')) {
            $response->setSession(app('session.store'));
        }

        return $response;
    }

    private function notificationsAvailable(): bool
    {
        return Schema::hasTable('notifications')
            && Schema::hasColumns('notifications', ['user_id', 'is_read']);
    }

    /**
     * Open a notification
     */
    public function open($id)
    {
        $customerId = (int) session('user_id');

        if (!$customerId) {
            return $this->redirectRelative('customer.login');
        }

        if (!$this->notificationsAvailable()) {
            return $this->redirectRelative('customer.dashboard');
        }

        $notif = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $customerId)
            ->first();

        if (!$notif) {
            return $this->redirectRelative('customer.dashboard')
                ->with('error', 'Notification not found.');
        }

        $update = ['is_read' => 1];

        if (Schema::hasColumn('notifications', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('notifications')
            ->where('id', $id)
            ->update($update);

        $type = strtolower((string) ($notif->type ?? ''));
        $reference = trim((string) ($notif->reference_code ?? ''));

        if ($type === 'review') {
            $params = $reference !== '' ? ['ref' => $reference] : [];

            return $this->redirectRelative('customer.reviews', $params);
        }

        if ($reference !== '' && Schema::hasTable('bookings')) {
            $bookingExists = DB::table('bookings')
                ->where('customer_id', $customerId)
                ->where('reference_code', $reference)
                ->exists();

            if ($bookingExists) {
                return $this->redirectRelative('customer.bookings.show', [
                    'reference' => $reference,
                ]);
            }
        }

        return $this->redirectRelative('customer.bookings');
    }

    /**
     * Mark all notifications as read
     */
    public function readAll(Request $request)
    {
        if (!$this->notificationsAvailable()) {
            return back();
        }

        $customerId = (int) session('user_id');

        if (!$customerId) {
            return back();
        }

        $update = ['is_read' => 1];

        if (Schema::hasColumn('notifications', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('notifications')
            ->where('user_id', $customerId)
            ->where('is_read', 0)
            ->update($update);

        return back();
    }

    /**
     * Clear all notifications
     */
    public function clear(Request $request)
    {
        if (!Schema::hasTable('notifications') || !Schema::hasColumn('notifications', 'user_id')) {
            return back();
        }

        $customerId = (int) session('user_id');

        if (!$customerId) {
            return back();
        }

        DB::table('notifications')
            ->where('user_id', $customerId)
            ->delete();

        return back();
    }
}
