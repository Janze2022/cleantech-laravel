<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\OtpMailer;

class ProviderOtpController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->email, 404);

        return view('provider.verify', [
            'email' => $request->email,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $provider = DB::table('service_providers')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (! $provider || Carbon::now()->gt($provider->otp_expires_at)) {
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP',
            ]);
        }

        DB::table('service_providers')
            ->where('id', $provider->id)
            ->update([
                'otp'             => null,
                'otp_expires_at'  => null,
                'is_verified'     => 1,
            ]);

        return redirect()->route('provider.pending');
    }

    /* =========================
       RESEND OTP
    ========================== */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $provider = DB::table('service_providers')
            ->where('email', $request->email)
            ->first();

        if (! $provider) {
            return back()->withErrors([
                'email' => 'Provider not found.',
            ]);
        }

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('service_providers')
            ->where('id', $provider->id)
            ->update([
                'otp'            => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

        // Send OTP email
        OtpMailer::sendProviderOtp($provider->email, $otp, 10);

        return redirect()->route('provider.verify', [
            'email'  => $provider->email,
            'resent' => 1,
        ]);
    }
}
