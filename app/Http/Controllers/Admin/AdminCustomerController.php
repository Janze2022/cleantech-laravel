<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminCustomerController extends Controller
{
    public function index()
    {
        $users = collect();

        if (Schema::hasTable('customers')) {
            $users = $users->merge(
                DB::table('customers')
                    ->select('id', 'name', 'email', DB::raw("'customer' as role"))
                    ->get()
            );
        }

        if (Schema::hasTable('service_providers')) {
            $users = $users->merge(
                DB::table('service_providers')
                    ->select(
                        'id',
                        DB::raw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) as name"),
                        'email',
                        DB::raw("'provider' as role")
                    )
                    ->get()
            );
        }

        if (Schema::hasTable('admins')) {
            $users = $users->merge(
                DB::table('admins')
                    ->select('id', 'name', 'email', DB::raw("'admin' as role"))
                    ->get()
            );
        }

        return view('admin.customers', compact('users'));
    }

    public function show($id)
    {
        return redirect()
            ->route('admin.customers')
            ->with('info', 'Use the accounts table below to review and edit users.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['customer', 'provider', 'admin'])],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $role = $validated['role'];

        $table = match ($role) {
            'provider' => 'service_providers',
            'admin' => 'admins',
            default => 'customers',
        };

        $data = [
            'email' => $validated['email'],
            'updated_at' => now(),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($role === 'provider') {
            $parts = preg_split('/\s+/', trim($validated['name']), 2);
            $data['first_name'] = $parts[0] ?? '';
            $data['last_name'] = $parts[1] ?? '';
        } else {
            $data['name'] = $validated['name'];
        }

        DB::table($table)->where('id', $id)->update($data);

        $this->logAdminAction("Updated {$role} account", $table, $id);

        return back()->with('success','User updated successfully.');
    }

    // ✅ FIXED METHOD NAME (IMPORTANT)
    public function destroy(Request $request, $id)
    {
        try {
            if ($request->role === 'admin') {
                return back()->with('error','Admins cannot be deleted.');
            }

            $table = $request->role === 'provider'
                ? 'service_providers'
                : 'customers';

            DB::table($table)->where('id', $id)->delete();

            $this->logAdminAction("Deleted {$request->role} account", $table, $id);

            return back()->with('success','Account deleted.');

        } catch (\Exception $e) {
            return back()->with('error','Delete failed: '.$e->getMessage());
        }
    }

    protected function logAdminAction(string $action, string $table, int $targetId): void
    {
        if (!Schema::hasTable('admin_logs')) {
            return;
        }

        $data = [
            'action' => $action,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('admin_logs', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if (Schema::hasColumn('admin_logs', 'admin_id') && session()->has('admin_id')) {
            $data['admin_id'] = session('admin_id');
        }

        if (Schema::hasColumn('admin_logs', 'target_table')) {
            $data['target_table'] = $table;
        }

        if (Schema::hasColumn('admin_logs', 'target_id')) {
            $data['target_id'] = $targetId;
        }

        DB::table('admin_logs')->insert($data);
    }
}
