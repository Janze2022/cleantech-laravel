<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProviderPreRegisterController extends Controller
{
    // STEP 1A: Terms page
    public function terms()
    {
        // clear old flow if user restarts
        session()->forget('provider_terms_accepted');
        session()->forget('provider_step1');

        return view('provider.pre-register-terms');
    }

    // STEP 1A Submit: Accept terms
    public function acceptTerms(Request $request)
    {
        $request->validate([
            'agree' => 'accepted'
        ]);

        session(['provider_terms_accepted' => true]);

        return redirect()->route('provider.pre_register');
    }

    // STEP 1B: Basic info page
    public function show()
    {
        if (!session('provider_terms_accepted')) {
            return redirect()->route('provider.pre_register.terms');
        }

        return view('provider.pre-register');
    }

    // STEP 1B Submit: Store to session then go to /provider/register
    public function store(Request $request)
    {
        if (!session('provider_terms_accepted')) {
            return redirect()->route('provider.pre_register.terms');
        }

        $data = $request->validate([
            'is_stateless' => 'required|in:0,1',
            'is_refugee'   => 'required|in:0,1',
            'citizenship'  => 'required|string|max:80',

            'first_name'   => 'required|string|max:50',
            'middle_name'  => 'nullable|string|max:50',
            'no_middle'    => 'nullable|in:1',
            'last_name'    => 'required|string|max:50',
            'suffix'       => 'nullable|string|max:10',

            'dob_month'    => 'required|integer|min:1|max:12',
            'dob_day'      => 'required|integer|min:1|max:31',
            'dob_year'     => 'required|integer|min:1900|max:2100',

            'civil_status' => 'required|in:Single,Married,Widowed,Separated',
            'gender'       => 'required|in:Male,Female',

            'email'        => 'required|email',
        ]);

        if (!empty($data['no_middle'])) {
            $data['middle_name'] = null;
        }

        $dob = sprintf('%04d-%02d-%02d', $data['dob_year'], $data['dob_month'], $data['dob_day']);

        session([
            'provider_step1' => [
                'is_stateless'  => (int)$data['is_stateless'],
                'is_refugee'    => (int)$data['is_refugee'],
                'citizenship'   => $data['citizenship'],

                'first_name'    => $data['first_name'],
                'middle_name'   => $data['middle_name'],
                'last_name'     => $data['last_name'],
                'suffix'        => $data['suffix'] ?? null,

                'date_of_birth' => $dob,
                'civil_status'  => $data['civil_status'],
                'gender'        => $data['gender'],

                'email'         => $data['email'],
            ]
        ]);

        return redirect()->route('provider.register');
    }
}
