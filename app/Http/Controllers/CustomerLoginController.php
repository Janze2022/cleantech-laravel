<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerLoginController extends Controller
{
    public function show()
    {
        return view('customer.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $customer = DB::table('customers')
            ->where('email', $credentials['email'])
            ->first();

        if (!$customer) {
            return back()->withErrors([
                'email' => 'Invalid email or password.'
            ])->withInput();
        }

        // ❌ Wrong password
        if (!Hash::check($credentials['password'], $customer->password)) {
            return back()->withErrors([
                'email' => 'Invalid email or password.'
            ])->withInput();
        }

        // ❌ Not verified
        if ((int) $customer->is_verified !== 1) {
            return back()->withErrors([
                'email' => 'Account not verified. Please complete OTP verification.'
            ]);
        }

        // ❌ Not active
        if ($customer->status !== 'active') {
            return back()->withErrors([
                'email' => 'Account is not active.'
            ]);
        }

        // ✅ SUCCESS — store session (Laravel way)
        session([
            'user_id' => $customer->id,
            'role'    => 'customer',
            'name'    => $customer->name,
        ]);

        return redirect()->route('customer.dashboard'); // change later to dashboard
    }
}
