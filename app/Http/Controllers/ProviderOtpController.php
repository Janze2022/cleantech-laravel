<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use App\Services\OtpMailer;

class ProviderOtpController extends Controller
{
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const RESEND_MAX_ATTEMPTS = 5;
    private const RESEND_WINDOW_SECONDS = 600;

    public function show(Request $request)
    {
        abort_unless($request->email, 404);

        return view('provider.verify', [
            'email' => $request->email,
            'otpCooldown' => max(0, (int) session('otp_cooldown', 0)),
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

    public function resend(Request $request)
    {
        $email = $request->validate([
            'email' => 'required|email',
        ])['email'];

        ['cooldown' => $cooldownKey, 'window' => $windowKey] = $this->resendLimiterKeys($request, $email);

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);

            return $this->cooldownRedirect(
                $email,
                $seconds,
                'Please wait ' . $this->formatCooldown($seconds) . ' before resending a new OTP.'
            );
        }

        if (RateLimiter::tooManyAttempts($windowKey, self::RESEND_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($windowKey);

            return $this->cooldownRedirect(
                $email,
                $seconds,
                'Too many resend attempts. Please try again in ' . $this->formatCooldown($seconds) . '.'
            );
        }

        $provider = DB::table('service_providers')
            ->where('email', $email)
            ->first();

        if (! $provider) {
            return back()->withErrors([
                'email' => 'Provider not found.',
            ]);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('service_providers')
            ->where('id', $provider->id)
            ->update([
                'otp'            => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

        OtpMailer::sendProviderOtp($provider->email, $otp, 10);

        RateLimiter::hit($cooldownKey, self::RESEND_COOLDOWN_SECONDS);
        RateLimiter::hit($windowKey, self::RESEND_WINDOW_SECONDS);

        return redirect()->route('provider.verify', [
            'email'  => $provider->email,
            'resent' => 1,
        ])->with('otp_cooldown', self::RESEND_COOLDOWN_SECONDS);
    }

    private function resendLimiterKeys(Request $request, string $email): array
    {
        $identity = sha1(strtolower(trim($email)) . '|' . $request->ip());

        return [
            'cooldown' => 'provider-verify-resend-cooldown:' . $identity,
            'window' => 'provider-verify-resend-window:' . $identity,
        ];
    }

    private function cooldownRedirect(string $email, int $seconds, string $message)
    {
        return redirect()
            ->route('provider.verify', ['email' => $email])
            ->withErrors(['otp' => $message])
            ->with('otp_cooldown', $seconds);
    }

    private function formatCooldown(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        }

        $minutes = intdiv($seconds, 60);
        $remaining = $seconds % 60;

        if ($remaining === 0) {
            return $minutes . ' minute' . ($minutes === 1 ? '' : 's');
        }

        return $minutes . 'm ' . $remaining . 's';
    }
}
