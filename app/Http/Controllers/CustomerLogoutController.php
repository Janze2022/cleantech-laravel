<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerLogoutController extends Controller
{
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
