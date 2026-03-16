<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfCustomerLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        if (
            session()->has('user_id') &&
            session('role') === 'customer'
        ) {
            return redirect()->route('customer.dashboard');
        }

        return $next($request);
    }
}
