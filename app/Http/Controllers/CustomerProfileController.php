<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
{
    public function show()
    {
        $customerId = session('user_id');

        abort_unless($customerId, 403, 'Customer session missing.');

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->first();

        abort_unless($customer, 404);

        return view('customer.profile', compact('customer'));
    }

    public function update(Request $request)
    {
        $customerId = session('user_id');

        abort_unless($customerId, 403, 'Customer session missing.');

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^09\d{9}$/'],
        ], [
            'phone.regex' => 'Mobile number must start with 09 and contain exactly 11 digits.',
        ]);

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->first();

        abort_unless($customer, 404);

        DB::table('customers')
            ->where('id', $customerId)
            ->update([
                'name' => trim($request->name),
                'phone' => trim($request->phone),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Profile details updated successfully.');
    }

    public function updateImage(Request $request)
    {
        $customerId = session('user_id');

        abort_unless($customerId, 403, 'Customer session missing.');

        $request->validate([
            'profile_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->first();

        abort_unless($customer, 404);

        if (!$request->hasFile('profile_image')) {
            return back()->withErrors([
                'profile_image' => 'Please choose an image first.',
            ]);
        }

        $file = $request->file('profile_image');

        if (!$file->isValid()) {
            return back()->withErrors([
                'profile_image' => 'Uploaded image is invalid.',
            ]);
        }

        $destinationPath = public_path('uploads/customers');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        if (!empty($customer->profile_image)) {
            $oldFile = $destinationPath . DIRECTORY_SEPARATOR . $customer->profile_image;

            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = 'customer_' . $customerId . '_' . time() . '.' . $extension;

        $file->move($destinationPath, $filename);

        DB::table('customers')
            ->where('id', $customerId)
            ->update([
                'profile_image' => $filename,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $customerId = session('user_id');

        abort_unless($customerId, 403, 'Customer session missing.');

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->select('password')
            ->first();

        abort_unless($customer, 404);

        if (!Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        DB::table('customers')
            ->where('id', $customerId)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Password changed successfully.');
    }
}