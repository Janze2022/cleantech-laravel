<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerSession
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('user_id') || session('role') !== 'customer') {
            return redirect()->route('customer.login');
        }

        return $next($request);
    }
}
