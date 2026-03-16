<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminCustomerController extends Controller
{
    public function index()
    {
        $users = collect()
            ->merge(
                DB::table('customers')
                    ->select('id','name','email',DB::raw("'customer' as role"))
                    ->get()
            )
            ->merge(
                DB::table('service_providers')
                    ->select(
                        'id',
                        DB::raw("CONCAT(first_name,' ',last_name) as name"),
                        'email',
                        DB::raw("'provider' as role")
                    )
                    ->get()
            )
            ->merge(
                DB::table('admins')
                    ->select('id','name','email',DB::raw("'admin' as role"))
                    ->get()
            );

        return view('admin.customers', compact('users'));
    }

    public function update(Request $request, $id)
    {
        $role = $request->role;

        $table = match ($role) {
            'provider' => 'service_providers',
            'admin' => 'admins',
            default => 'customers',
        };

        $data = [
            'email' => $request->email,
            'updated_at' => now(),
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        if ($role === 'provider') {
            [$first, $last] = explode(' ', $request->name, 2);
            $data['first_name'] = $first;
            $data['last_name'] = $last ?? '';
        } else {
            $data['name'] = $request->name;
        }

        DB::table($table)->where('id', $id)->update($data);

        DB::table('admin_logs')->insert([
            'admin_id' => session('admin_id'),
            'action' => "Updated {$role} account",
            'target_table' => $table,
            'target_id' => $id,
            'created_at' => now(),
        ]);

        return back()->with('success','User updated successfully.');
    }

    public function delete(Request $request, $id)
    {
        if ($request->role === 'admin') {
            return back()->with('error','Admins cannot be deleted.');
        }

        $table = $request->role === 'provider'
            ? 'service_providers'
            : 'customers';

        DB::table($table)->where('id', $id)->delete();

        DB::table('admin_logs')->insert([
            'admin_id' => session('admin_id'),
            'action' => "Deleted {$request->role} account",
            'target_table' => $table,
            'target_id' => $id,
            'created_at' => now(),
        ]);

        return back()->with('success','Account deleted.');
    }
}
