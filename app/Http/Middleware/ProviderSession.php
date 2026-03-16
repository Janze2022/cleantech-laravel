<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class ProviderSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('provider_id')) {
            return redirect()->route('provider.login');
        }

        $provider = DB::table('service_providers')
            ->where('id', session('provider_id'))
            ->first();

        if (!$provider) {
            session()->forget('provider_id');
            return redirect()->route('provider.login');
        }

        // 🔥 ALLOW LOGOUT EVEN IF PENDING
        if ($provider->status === 'Pending') {
            if (
                !$request->routeIs('provider.pending') &&
                !$request->routeIs('provider.logout')
            ) {
                return redirect()->route('provider.pending');
            }
        }

        if (in_array($provider->status, ['Rejected', 'Suspended'])) {
            session()->forget('provider_id');

            return redirect()
                ->route('provider.login')
                ->withErrors([
                    'email' => 'Your provider account is not allowed to access the system.'
                ]);
        }

        return $next($request);
    }
}
