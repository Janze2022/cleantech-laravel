<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminProviderController extends Controller
{
    public function index()
    {
        $providers = DB::table('service_providers')
            ->select(
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'email',
                'citizenship',
                'is_stateless',
                'is_refugee',
                'date_of_birth',
                'civil_status',
                'gender',
                'phone',
                'country',
                'region',
                'province',
                'city',
                'barangay',
                'address',
                'emergency_name',
                'emergency_phone',
                'id_type',
                'id_image',
                'profile_image',
                'status',
                'is_verified',
                'created_at',
                'updated_at'
            )
            ->orderByDesc('created_at')
            ->get();

        return view('admin.providers.index', compact('providers'));
    }

    public function approve($id)
    {
        $this->updateStatus($id, 'Approved');
        return back()->with('success', 'Provider approved.');
    }

    public function reject($id)
    {
        $this->updateStatus($id, 'Rejected');
        return back()->with('success', 'Provider rejected.');
    }

    public function suspend($id)
    {
        $this->updateStatus($id, 'Suspended');
        return back()->with('success', 'Provider suspended.');
    }

    public function unapprove($id)
    {
        $this->updateStatus($id, 'Pending');
        return back()->with('success', 'Provider set back to pending.');
    }

    public function document($id)
    {
        $provider = DB::table('service_providers')
            ->where('id', $id)
            ->select('id', 'id_image')
            ->first();

        abort_if(!$provider || !$provider->id_image, 404, 'Document not found.');

        $path = $this->normalizePublicPath($provider->id_image);

        if ($path && Storage::disk('public')->exists($path)) {
            $disk = Storage::disk('public');
        } else {
            abort(404, 'Document not found.');
        }

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';
        $stream = $disk->readStream($path);

        abort_if(!$stream, 404, 'Document not found.');

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function updateStatus($id, $status)
    {
        DB::table('service_providers')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);

        if (Schema::hasTable('admin_logs')) {
            $data = [
                'action' => "Provider status set to {$status}",
                'created_at' => now(),
            ];

            if (Schema::hasColumn('admin_logs', 'updated_at')) {
                $data['updated_at'] = now();
            }

            if (Schema::hasColumn('admin_logs', 'admin_id') && session()->has('admin_id')) {
                $data['admin_id'] = session('admin_id');
            }

            if (Schema::hasColumn('admin_logs', 'target_table')) {
                $data['target_table'] = 'service_providers';
            }

            if (Schema::hasColumn('admin_logs', 'target_id')) {
                $data['target_id'] = $id;
            }

            DB::table('admin_logs')->insert($data);
        }
    }

    private function normalizePublicPath(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = str_replace('\\', '/', trim($value));

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $value = parse_url($value, PHP_URL_PATH) ?: $value;
        }

        $value = ltrim($value, '/');

        if (str_starts_with($value, 'storage/')) {
            $value = substr($value, 8);
        }

        return $value;
    }
}
