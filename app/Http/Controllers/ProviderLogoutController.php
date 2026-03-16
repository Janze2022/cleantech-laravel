<?php

namespace App\Http\Controllers;

class ProviderLogoutController extends Controller
{
    public function logout()
    {
        session()->forget([
            'provider_id',
            'name',
            'role'
        ]);

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('home');
    }
}
