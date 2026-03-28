<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProviderLogoutController extends Controller
{
    public function logout(Request $request)
    {
        $request->session()->forget([
            'provider_id',
            'name',
            'provider_name',
            'role'
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
