<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RedirectIfAdminLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        $adminId = session('admin_id');

        if (!$adminId || session('role') !== 'admin') {
            session()->forget(['admin_id', 'admin_name', 'admin_email']);

            return $next($request);
        }

        if (!Schema::hasTable('admins')) {
            session()->forget(['admin_id', 'admin_name', 'admin_email', 'role']);

            return $next($request);
        }

        $adminExists = DB::table('admins')
            ->where('id', $adminId)
            ->exists();

        if ($adminExists) {
            return redirect()->route('admin.dashboard');
        }

        session()->forget(['admin_id', 'admin_name', 'admin_email', 'role']);

        return $next($request);
    }
}
