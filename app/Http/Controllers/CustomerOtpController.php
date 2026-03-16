<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\OtpMailer;

class CustomerOtpController extends Controller
{
    public function show(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            abort(404);
        }

        return view('customer.verify', compact('email'));
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $customer = DB::table('customers')
            ->where('email', $data['email'])
            ->whereNotNull('otp')
            ->first();

        if (!$customer) {
            return back()->withErrors([
                'otp' => 'No active OTP found. Please request a new one.'
            ]);
        }

        if ($customer->otp !== $data['otp']) {
            return back()->withErrors([
                'otp' => 'Invalid OTP.'
            ]);
        }

        if (
            !$customer->otp_expires_at ||
            Carbon::now()->gt(Carbon::parse($customer->otp_expires_at))
        ) {
            return back()->withErrors([
                'otp' => 'OTP has expired. Please resend.'
            ]);
        }

        DB::table('customers')
            ->where('email', $data['email'])
            ->update([
                'is_verified' => 1,
                'status' => 'active',
                'otp' => null,
                'otp_expires_at' => null,
                'updated_at' => now(),
            ]);

        return redirect()->route('customer.verified');
    }

    public function resend(Request $request)
    {
        $email = $request->validate([
            'email' => 'required|email',
        ])['email'];

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(5);

        $updated = DB::table('customers')
            ->where('email', $email)
            ->where('is_verified', 0)
            ->update([
                'otp' => $otp,
                'otp_expires_at' => $expiresAt,
            ]);

        if (!$updated) {
            return back()->withErrors([
                'otp' => 'Account already verified or not found.'
            ]);
        }

        OtpMailer::sendRegister($email, $otp);

        return redirect()->route('customer.verify', [
            'email' => $email,
            'resent' => 1
        ]);
    }
}
