<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerLoginController extends Controller
{
    protected function clearRoleSessions(Request $request): void
    {
        $request->session()->forget([
            'user_id',
            'provider_id',
            'admin_id',
            'name',
            'role',
            'admin_name',
            'admin_email',
        ]);
    }

    public function show()
    {
        return view('customer.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $customer = DB::table('customers')
            ->where('email', $credentials['email'])
            ->first();

        if (!$customer) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->withInput();
        }

        if (!Hash::check($credentials['password'], $customer->password)) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->withInput();
        }

        if ((int) $customer->is_verified !== 1) {
            return back()->withErrors([
                'email' => 'Account not verified. Please complete OTP verification.',
            ]);
        }

        if ($customer->status !== 'active') {
            return back()->withErrors([
                'email' => 'Account is not active.',
            ]);
        }

        $this->clearRoleSessions($request);
        $request->session()->regenerate();

        $request->session()->put([
            'user_id' => $customer->id,
            'role' => 'customer',
            'name' => $customer->name,
        ]);

        return redirect()->route('customer.dashboard');
    }
}
