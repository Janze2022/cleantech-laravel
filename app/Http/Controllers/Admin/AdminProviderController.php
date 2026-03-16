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

        $path = ltrim($provider->id_image, '/');

        if (Storage::disk('public')->exists($path)) {
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
            DB::table('admin_logs')->insert([
                'admin_id'     => session('admin_id'),
                'action'       => "Provider status set to {$status}",
                'target_table' => 'service_providers',
                'target_id'    => $id,
                'created_at'   => now(),
            ]);
        }
    }
}