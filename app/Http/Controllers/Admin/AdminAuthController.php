<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
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

    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = DB::table('admins')
            ->where('email', $request->email)
            ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['email' => 'Invalid admin credentials']);
        }

        $this->clearRoleSessions($request);
        $request->session()->regenerate();

        $request->session()->put([
            'admin_id' => $admin->id,
            'admin_name' => $admin->name ?? 'Admin',
            'admin_email' => $admin->email,
            'role' => 'admin',
        ]);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $this->clearRoleSessions($request);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
