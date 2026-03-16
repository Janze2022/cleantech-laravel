<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProviderProfileController extends Controller
{
    public function show()
    {
        $providerId = session('provider_id');

        if (!$providerId) {
            return redirect()->route('provider.login')->with('error', 'Please log in first.');
        }

        $provider = DB::table('service_providers')
            ->where('id', $providerId)
            ->first();

        if (!$provider) {
            abort(404, 'Provider not found.');
        }

        return view('provider.profile', compact('provider'));
    }

    public function update(Request $request)
    {
        $providerId = session('provider_id');

        if (!$providerId) {
            return redirect()->route('provider.login')->with('error', 'Please log in first.');
        }

        $provider = DB::table('service_providers')
            ->where('id', $providerId)
            ->first();

        if (!$provider) {
            return back()->with('error', 'Provider not found.');
        }

        if (!Schema::hasColumn('service_providers', 'profile_image')) {
            return back()->with('error', 'The profile_image column does not exist in service_providers table.');
        }

        $data = $request->validate([
            'phone' => ['required', 'regex:/^09\d{9}$/'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ], [
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Phone number must be a valid Philippine mobile number (example: 09123456789).',
            'profile_image.image' => 'The uploaded file must be an image.',
            'profile_image.mimes' => 'Profile image must be a JPG, JPEG, PNG, GIF, or WEBP file.',
            'profile_image.max' => 'Profile image must not exceed 5MB.',
        ]);

        $updateData = [
            'phone' => $data['phone'],
            'updated_at' => now(),
        ];

        try {
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');

                if (!$file || !$file->isValid()) {
                    return back()->with('error', 'The uploaded profile image is invalid.');
                }

                // Delete old image if it exists
                $oldPath = $this->normalizeProfileImagePath($provider->profile_image ?? null);
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }

                // Save new image
                $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $filename = (string) Str::uuid() . '.' . $extension;

                $storedPath = $file->storeAs('providers', $filename, 'public');

                if (!$storedPath) {
                    return back()->with('error', 'Image upload failed while saving to storage.');
                }

                // ALWAYS save as: providers/filename.ext
                $updateData['profile_image'] = str_replace('\\', '/', $storedPath);

                Log::info('Provider profile image uploaded', [
                    'provider_id' => $providerId,
                    'stored_path' => $storedPath,
                ]);
            }

            DB::table('service_providers')
                ->where('id', $providerId)
                ->update($updateData);

            $freshProvider = DB::table('service_providers')
                ->where('id', $providerId)
                ->first();

            if ($request->hasFile('profile_image')) {
                $savedPath = $this->normalizeProfileImagePath($freshProvider->profile_image ?? null);

                if (!$savedPath) {
                    return back()->with('error', 'Image uploaded, but database did not save the profile_image path.');
                }

                if (!Storage::disk('public')->exists($savedPath)) {
                    return back()->with('error', 'Database saved the image path, but the file was not found in storage: ' . $savedPath);
                }

                return back()->with('success', 'Profile image and details updated successfully.');
            }

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Provider profile update failed', [
                'provider_id' => $providerId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Public image route handler
     * Example route:
     * Route::get('/provider/image/{filename}', [ProviderProfileController::class, 'publicImage'])
     *     ->where('filename', '.*')
     *     ->name('provider.image');
     */
    public function publicImage($filename)
    {
        return $this->serveProviderImage($filename);
    }

    public function image($filename)
    {
        return $this->serveProviderImage($filename);
    }

    protected function serveProviderImage($filename)
    {
        $path = $this->normalizeProfileImagePath($filename);

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    /**
     * Normalizes anything like:
     * - providers/abc.jpg
     * - /providers/abc.jpg
     * - storage/providers/abc.jpg
     * - http://site/storage/providers/abc.jpg
     * - abc.jpg
     * into:
     * - providers/abc.jpg
     */
    protected function normalizeProfileImagePath($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = str_replace('\\', '/', trim($value));

        // If full URL, get only path
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $parsedPath = parse_url($value, PHP_URL_PATH);
            $value = $parsedPath ?: $value;
        }

        $value = ltrim($value, '/');

        // remove leading storage/
        if (Str::startsWith($value, 'storage/')) {
            $value = substr($value, 8);
        }

        // if already providers/filename.ext
        if (Str::startsWith($value, 'providers/')) {
            return $value;
        }

        // fallback to basename only
        return 'providers/' . basename($value);
    }

    public function changePassword(Request $request)
    {
        $providerId = session('provider_id');

        if (!$providerId) {
            return redirect()->route('provider.login')->with('error', 'Please log in first.');
        }

        $provider = DB::table('service_providers')
            ->where('id', $providerId)
            ->first();

        if (!$provider) {
            return back()->with('error', 'Provider not found.');
        }

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Current password is required.',
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if (!Hash::check($request->current_password, $provider->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        DB::table('service_providers')
            ->where('id', $providerId)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Password updated successfully.');
    }
}