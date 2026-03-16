<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Services\OtpMailer;

class ProviderForgotPasswordController extends Controller
{
    public function show()
    {
        return view('provider.forgot_password');
    }

    public function sendOtp(Request $request)
    {
        $provider = DB::table('service_providers')
            ->where('email', $request->email)
            ->first();

        if (!$provider) {
            return back()->withErrors(['email' => 'Email not found']);
        }

        $otp = str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);

        DB::table('service_providers')
            ->where('id', $provider->id)
            ->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

        OtpMailer::sendProviderResetOtp($request->email, $otp, 10);

        return redirect()->route('provider.reset.verify', ['email'=>$request->email]);
    }

    public function verifyForm(Request $request)
    {
        return view('provider.reset_verify', ['email'=>$request->email]);
    }

    public function verifyOtp(Request $request)
    {
        $provider = DB::table('service_providers')
            ->where('email',$request->email)
            ->where('otp',$request->otp)
            ->first();

        if (!$provider) {
            return back()->withErrors(['otp'=>'Invalid OTP']);
        }

        session(['provider_reset_email'=>$request->email]);

        return redirect()->route('provider.reset.password');
    }

    public function resetForm()
    {
        return view('provider.reset_password');
    }

    public function reset(Request $request)
    {
        DB::table('service_providers')
            ->where('email', session('provider_reset_email'))
            ->update([
                'password'=>Hash::make($request->password),
                'otp'=>null,
            ]);

        session()->forget('provider_reset_email');

        return redirect()->route('provider.reset.success');
    }

    public function success()
    {
        return view('provider.reset_success');
    }
}
