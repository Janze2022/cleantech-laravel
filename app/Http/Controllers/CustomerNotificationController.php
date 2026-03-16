<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerNotificationController extends Controller
{
    /**
     * Open a notification
     */
    public function open($id)
    {
        $customerId = session('user_id');

        if (!$customerId) {
            return redirect()->route('customer.login');
        }

        $notif = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $customerId)
            ->first();

        if (!$notif) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Notification not found.');
        }

        // Mark notification as read
        DB::table('notifications')
            ->where('id', $id)
            ->update([
                'is_read' => 1,
                'updated_at' => now()
            ]);

        /**
         * Redirect depending on notification type
         */

        // Review notification
        if (($notif->type ?? null) === 'review') {
            return redirect()->route('customer.reviews', [
                'ref' => $notif->reference_code
            ]);
        }

        // Booking notification → open booking details
        if (!empty($notif->reference_code)) {
            return redirect()->route('customer.bookings.show', [
                'reference' => $notif->reference_code
            ]);
        }

        // Fallback
        return redirect()->route('customer.bookings')
            ->with('success', 'Booking status updated.');
    }

    /**
     * Mark all notifications as read
     */
    public function readAll(Request $request)
    {
        $customerId = session('user_id');

        if (!$customerId) {
            return back();
        }

        DB::table('notifications')
            ->where('user_id', $customerId)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
                'updated_at' => now()
            ]);

        return back();
    }

    /**
     * Clear all notifications
     */
    public function clear(Request $request)
    {
        $customerId = session('user_id');

        if (!$customerId) {
            return back();
        }

        DB::table('notifications')
            ->where('user_id', $customerId)
            ->delete();

        return back();
    }
}