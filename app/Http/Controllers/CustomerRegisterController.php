<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Services\OtpMailer;
use Throwable;

class CustomerRegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function show()
    {
        if (session()->has('user_id')) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.register');
    }

    /**
     * Handle registration
     */
    public function store(Request $request)
    {
        if (session()->has('user_id')) {
            return redirect()->route('customer.dashboard');
        }

        // ✅ VALIDATION (FIXED + CONFIRM PASSWORD)
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[A-Za-z\s\'\-]+$/',
                ],
                'email' => 'required|email|max:100',
                'phone' => ['required', 'regex:/^09\d{9}$/'],
                'password' => 'required|min:6|confirmed',
            ],
            [
                'name.regex'           => 'Name may only contain letters, spaces, hyphens, and apostrophes.',
                'phone.regex'          => 'Mobile number must start with 09 and contain exactly 11 digits.',
                'password.confirmed'   => 'Passwords do not match.',
            ]
        );

        // ✅ GENERATE 6-DIGIT OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(5);

        // 🔎 CHECK EXISTING CUSTOMER
        $customer = DB::table('customers')
            ->where('email', $validated['email'])
            ->first();

        // ❌ Already active
        if ($customer && $customer->status === 'active') {
            return back()
                ->withErrors(['email' => 'This email is already registered and verified.'])
                ->withInput();
        }

        // 🔄 Update existing (inactive) OR create new
        if ($customer) {
            DB::table('customers')
                ->where('id', $customer->id)
                ->update([
                    'otp'            => $otp,
                    'otp_expires_at' => $expiresAt,
                    'updated_at'     => now(),
                ]);
        } else {
            DB::table('customers')->insert([
                'name'           => $validated['name'],
                'email'          => $validated['email'],
                'phone'          => $validated['phone'],
                'password'       => Hash::make($validated['password']),
                'otp'            => $otp,
                'otp_expires_at' => $expiresAt,
                'status'         => 'inactive',
                'is_verified'    => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // 📧 SEND OTP (SAFE)
        try {
            OtpMailer::sendRegister(
                $validated['email'],
                $otp,
                5
            );
        } catch (Throwable $e) {
            logger()->error('OTP Send Failed: ' . $e->getMessage());

            return back()
                ->withErrors(['email' => 'Unable to send OTP. Please try again later.'])
                ->withInput();
        }

        // ✅ REDIRECT TO VERIFY
        return redirect()->route('customer.verify', [
            'email' => $validated['email'],
        ]);
    }
}
