<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $oldStoragePath = $this->normalizeProfileImagePath($customer->profile_image ?? null);
        $legacyPath = public_path('uploads/customers/' . basename((string) ($customer->profile_image ?? '')));

        if ($oldStoragePath && Storage::disk('public')->exists($oldStoragePath)) {
            Storage::disk('public')->delete($oldStoragePath);
        }

        if (is_file($legacyPath)) {
            @unlink($legacyPath);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = 'customer_' . $customerId . '_' . Str::uuid() . '.' . $extension;
        $storedPath = $file->storeAs('customers', $filename, 'public');

        if (!$storedPath) {
            return back()->withErrors([
                'profile_image' => 'Image upload failed while saving to storage.',
            ]);
        }

        $storedPath = str_replace('\\', '/', $storedPath);

        DB::table('customers')
            ->where('id', $customerId)
            ->update([
                'profile_image' => $storedPath,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function publicImage($filename)
    {
        return $this->serveProfileImage($filename);
    }

    protected function serveProfileImage($filename)
    {
        $path = $this->normalizeProfileImagePath($filename);

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        $legacyPath = public_path('uploads/customers/' . basename((string) $filename));

        if (is_file($legacyPath)) {
            return response()->file($legacyPath);
        }

        abort(404);
    }

    protected function normalizeProfileImagePath($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = str_replace('\\', '/', trim((string) $value));

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $value = parse_url($value, PHP_URL_PATH) ?: $value;
        }

        $value = ltrim($value, '/');

        if (Str::startsWith($value, 'storage/')) {
            $value = substr($value, 8);
        }

        if (Str::startsWith($value, 'customers/')) {
            return $value;
        }

        return 'customers/' . basename($value);
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
