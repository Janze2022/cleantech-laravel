<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProviderLoginController extends Controller
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

    /**
     * Show provider login form.
     */
    public function show()
    {
        return view('provider.login');
    }

    /**
     * Handle provider login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $provider = DB::table('service_providers')
            ->where('email', $request->email)
            ->first();

        if (!$provider || !Hash::check($request->password, $provider->password)) {
            return back()->withErrors([
                'email' => 'Invalid credentials',
            ])->withInput();
        }

        $this->clearRoleSessions($request);
        $request->session()->regenerate();

        $request->session()->put([
            'provider_id' => $provider->id,
            'name' => $provider->first_name,
            'role' => 'provider',
        ]);

        if ($provider->status !== 'Approved') {
            return redirect()->route('provider.pending');
        }

        return redirect()->route('provider.dashboard');
    }
}
