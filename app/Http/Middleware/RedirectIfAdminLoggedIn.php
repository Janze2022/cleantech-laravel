<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfAdminLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
