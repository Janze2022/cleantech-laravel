<?php

namespace App\Http\Controllers;

use App\Services\OtpMailer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class CustomerForgotPasswordController extends Controller
{
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const RESEND_MAX_ATTEMPTS = 5;
    private const RESEND_WINDOW_SECONDS = 600;

    public function show()
    {
        return view('customer.forgot_password');
    }

    public function sendOtp(Request $request)
    {
        $email = $request->validate([
            'email' => 'required|email',
        ])['email'];

        return $this->issueForgotOtp($request, $email, false);
    }

    public function resendOtp(Request $request)
    {
        $email = $request->validate([
            'email' => 'required|email',
        ])['email'];

        return $this->issueForgotOtp($request, $email, true);
    }

    public function showVerifyOtp(Request $request)
    {
        abort_unless($request->email, 404);

        return view('customer.reset_verify', [
            'email' => $request->email,
            'otpCooldown' => max(0, (int) session('otp_cooldown', 0)),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $customer = DB::table('customers')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$customer) {
            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        if (Carbon::now()->gt($customer->otp_expires_at)) {
            return back()->withErrors(['otp' => 'OTP has expired.']);
        }

        session([
            'password_reset_email' => $request->email,
        ]);

        return redirect()->route('customer.forgot.reset');
    }

    public function showResetForm()
    {
        abort_unless(session()->has('password_reset_email'), 403);

        return view('customer.reset_password');
    }

    public function resetPassword(Request $request)
    {
        abort_unless(session()->has('password_reset_email'), 403);

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        DB::table('customers')
            ->where('email', session('password_reset_email'))
            ->update([
                'password'        => Hash::make($request->password),
                'otp'             => null,
                'otp_expires_at'  => null,
            ]);

        session()->forget('password_reset_email');

        return redirect()->route('customer.forgot.success');
    }

    public function success()
    {
        return view('customer.reset_success');
    }

    private function issueForgotOtp(Request $request, string $email, bool $fromVerify)
    {
        ['cooldown' => $cooldownKey, 'window' => $windowKey] = $this->resendLimiterKeys($request, $email);

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);

            return $this->cooldownRedirect(
                $email,
                $seconds,
                'Please wait ' . $this->formatCooldown($seconds) . ' before requesting another OTP.',
                $fromVerify
            );
        }

        if (RateLimiter::tooManyAttempts($windowKey, self::RESEND_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($windowKey);

            return $this->cooldownRedirect(
                $email,
                $seconds,
                'Too many OTP requests. Please try again in ' . $this->formatCooldown($seconds) . '.',
                $fromVerify
            );
        }

        $customer = DB::table('customers')
            ->where('email', $email)
            ->first();

        if (!$customer) {
            return $this->errorRedirect($email, 'No account found with that email.', $fromVerify);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('customers')
            ->where('id', $customer->id)
            ->update([
                'otp'            => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
                'updated_at'     => now(),
            ]);

        OtpMailer::sendForgotPassword($email, $otp, 10);

        RateLimiter::hit($cooldownKey, self::RESEND_COOLDOWN_SECONDS);
        RateLimiter::hit($windowKey, self::RESEND_WINDOW_SECONDS);

        return redirect()
            ->route('customer.forgot.verify', ['email' => $email])
            ->with('resent', 1)
            ->with('otp_cooldown', self::RESEND_COOLDOWN_SECONDS);
    }

    private function resendLimiterKeys(Request $request, string $email): array
    {
        $identity = sha1(strtolower(trim($email)) . '|' . $request->ip());

        return [
            'cooldown' => 'customer-forgot-otp-cooldown:' . $identity,
            'window' => 'customer-forgot-otp-window:' . $identity,
        ];
    }

    private function cooldownRedirect(string $email, int $seconds, string $message, bool $fromVerify)
    {
        $redirect = $fromVerify
            ? redirect()->route('customer.forgot.verify', ['email' => $email])
            : redirect()->route('customer.forgot');

        $response = $redirect
            ->withErrors([$fromVerify ? 'otp' : 'email' => $message])
            ->with('otp_cooldown', $seconds);

        if (!$fromVerify) {
            $response->withInput(['email' => $email]);
        }

        return $response;
    }

    private function errorRedirect(string $email, string $message, bool $fromVerify)
    {
        $redirect = $fromVerify
            ? redirect()->route('customer.forgot.verify', ['email' => $email])
            : redirect()->route('customer.forgot');

        $response = $redirect->withErrors([$fromVerify ? 'otp' : 'email' => $message]);

        if (!$fromVerify) {
            $response->withInput(['email' => $email]);
        }

        return $response;
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
