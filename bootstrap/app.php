<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\CustomerSession;
use App\Http\Middleware\RedirectIfCustomerLoggedIn;

use App\Http\Middleware\ProviderSession;
use App\Http\Middleware\RedirectIfProviderLoggedIn;

use App\Http\Middleware\AdminSession;
use App\Http\Middleware\RedirectIfAdminLoggedIn;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([

            // =========================
            // CUSTOMER
            // =========================
            'customer.session' => CustomerSession::class,
            'customer.guest'   => RedirectIfCustomerLoggedIn::class,

            // =========================
            // PROVIDER
            // =========================
            'provider.session' => ProviderSession::class,
            'provider.guest'   => RedirectIfProviderLoggedIn::class,

            // =========================
            // ADMIN (SUPER ADMIN)
            // =========================
            'admin.session' => AdminSession::class,
            'admin.guest'   => RedirectIfAdminLoggedIn::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
