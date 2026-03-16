<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    protected function getAdminFromSession()
    {
        $adminId = session('admin_id');

        if (!$adminId) {
            abort(403, 'Admin session not found.');
        }

        return Admin::findOrFail($adminId);
    }

    public function index()
    {
        $admin = $this->getAdminFromSession();

        return view('admin.profile', compact('admin'));
    }

    public function update(Request $request)
    {
        $admin = $this->getAdminFromSession();

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->save();

        session([
            'admin_name' => $admin->name,
            'admin_email' => $admin->email,
        ]);

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $admin = $this->getAdminFromSession();

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $admin->password = Hash::make($request->password);
        $admin->save();

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Password updated successfully.');
    }
}