<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminSession
{
    public function handle(Request $request, Closure $next)
    {
        $adminId = session('admin_id');

        if (!$adminId || session('role') !== 'admin') {
            session()->forget(['admin_id', 'admin_name', 'admin_email']);
            return redirect()->route('admin.login');
        }

        if (!Schema::hasTable('admins')) {
            session()->forget(['admin_id', 'admin_name', 'admin_email', 'role']);
            return redirect()->route('admin.login');
        }

        $adminExists = DB::table('admins')
            ->where('id', $adminId)
            ->exists();

        if (!$adminExists) {
            session()->forget(['admin_id', 'admin_name', 'admin_email', 'role']);
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
