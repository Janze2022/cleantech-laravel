<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use App\Services\OtpMailer;

class CustomerOtpController extends Controller
{
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const RESEND_MAX_ATTEMPTS = 5;
    private const RESEND_WINDOW_SECONDS = 600;

    public function show(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            abort(404);
        }

        $otpCooldown = max(0, (int) session('otp_cooldown', 0));

        return view('customer.verify', compact('email', 'otpCooldown'));
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

        RateLimiter::hit($cooldownKey, self::RESEND_COOLDOWN_SECONDS);
        RateLimiter::hit($windowKey, self::RESEND_WINDOW_SECONDS);

        return redirect()->route('customer.verify', [
            'email' => $email,
            'resent' => 1
        ])->with('otp_cooldown', self::RESEND_COOLDOWN_SECONDS);
    }

    private function resendLimiterKeys(Request $request, string $email): array
    {
        $identity = sha1(strtolower(trim($email)) . '|' . $request->ip());

        return [
            'cooldown' => 'customer-verify-resend-cooldown:' . $identity,
            'window' => 'customer-verify-resend-window:' . $identity,
        ];
    }

    private function cooldownRedirect(string $email, int $seconds, string $message)
    {
        return redirect()
            ->route('customer.verify', ['email' => $email])
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
