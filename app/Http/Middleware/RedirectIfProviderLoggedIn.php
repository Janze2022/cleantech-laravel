<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfProviderLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('provider_id') && session('role') === 'provider') {
            return redirect()->route('provider.dashboard');
        }

        return $next($request);
    }
}
