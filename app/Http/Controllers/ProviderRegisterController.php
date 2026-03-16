<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\OtpMailer;

class ProviderRegisterController extends Controller
{
    public function show()
    {
        if (!session('provider_terms_accepted')) {
            return redirect()->route('provider.pre_register.terms');
        }

        if (!session()->has('provider_step1')) {
            return redirect()->route('provider.pre_register');
        }

        return view('provider.register', [
            'step1' => session('provider_step1'),
        ]);
    }

    public function store(Request $request)
    {
        if (!session('provider_terms_accepted')) {
            return redirect()->route('provider.pre_register.terms');
        }

        $step1 = session('provider_step1');
        if (!$step1) {
            return redirect()->route('provider.pre_register');
        }

        $request->validate([
            'phone'           => ['required', 'regex:/^09\d{9}$/'],
            'emergency_name'  => 'required|string|max:150',
            'emergency_phone' => ['required', 'regex:/^09\d{9}$/'],
            'password'        => 'required|min:6|confirmed',

            'region'          => 'required|string',
            'province'        => 'required|string',
            'city'            => 'required|string',
            'barangay'        => 'required|string',
            'address'         => 'required|string',

            // supports image + pdf
            'id_image'        => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
            'id_type'         => 'required|string|max:50',
        ]);

        $exists = DB::table('service_providers')
            ->where('email', $step1['email'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['email' => 'Email is already registered.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // SAVE TO PUBLIC DISK so admin blade can access via asset('storage/...')
            $idPath = $request->file('id_image')->store('providers/id', 'public');

            $otp = random_int(100000, 999999);

            DB::table('service_providers')->insert([
                'first_name'      => $step1['first_name'],
                'middle_name'     => $step1['middle_name'] ?? null,
                'last_name'       => $step1['last_name'],
                'suffix'          => $step1['suffix'] ?? null,
                'email'           => $step1['email'],

                'citizenship'     => $step1['citizenship'],
                'is_stateless'    => $step1['is_stateless'],
                'is_refugee'      => $step1['is_refugee'],
                'date_of_birth'   => $step1['date_of_birth'],
                'civil_status'    => $step1['civil_status'],
                'gender'          => $step1['gender'],

                'phone'           => $request->phone,

                'region'          => $request->region,
                'province'        => $request->province,
                'city'            => $request->city,
                'barangay'        => $request->barangay,
                'address'         => $request->address,

                'emergency_name'  => $request->emergency_name,
                'emergency_phone' => $request->emergency_phone,

                'password'        => Hash::make($request->password),

                'profile_image'   => null,
                'id_type'         => $request->id_type,
                'id_image'        => $idPath,

                'otp'             => $otp,
                'otp_expires_at'  => Carbon::now()->addMinutes(10),
                'status'          => 'Pending',
                'is_verified'     => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::commit();

            session()->forget('provider_step1');
            session()->forget('provider_terms_accepted');

            OtpMailer::sendProviderOtp($step1['email'], $otp, 10);

            return redirect()->route('provider.verify', ['email' => $step1['email']]);
        } catch (\Throwable $e) {
            DB::rollBack();

            if (isset($idPath)) {
                Storage::disk('public')->delete($idPath);
            }

            throw $e;
        }
    }
}