<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Services\OtpMailer;

class CustomerForgotPasswordController extends Controller
{
    /* =========================
       SHOW EMAIL FORM
    ========================== */
    public function show()
    {
        return view('customer.forgot_password');
    }

    /* =========================
       SEND OTP
    ========================== */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $customer = DB::table('customers')
            ->where('email', $request->email)
            ->first();

        if (!$customer) {
            return back()->withErrors([
                'email' => 'No account found with that email.',
            ]);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('customers')
            ->where('id', $customer->id)
            ->update([
                'otp'            => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

        OtpMailer::sendForgotPassword($request->email, $otp, 10);

        return redirect()->route('customer.forgot.verify', [
            'email' => $request->email,
        ]);
    }

    /* =========================
       OTP FORM
    ========================== */
    public function showVerifyOtp(Request $request)
    {
        abort_unless($request->email, 404);

        return view('customer.reset_verify', [
            'email' => $request->email,
        ]);
    }

    /* =========================
       VERIFY OTP
    ========================== */
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

    /* =========================
       NEW PASSWORD FORM
    ========================== */
    public function showResetForm()
    {
        abort_unless(session()->has('password_reset_email'), 403);

        return view('customer.reset_password');
    }

    /* =========================
       UPDATE PASSWORD
    ========================== */
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

    /* =========================
       SUCCESS PAGE
    ========================== */
    public function success()
    {
        return view('customer.reset_success');
    }
}
