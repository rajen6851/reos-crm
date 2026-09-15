<?php

use App\Http\Middleware\CheckCompanySubscription;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        App\Console\Commands\AutoTransferStaleLeads::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\RequestAndQueryLogger::class);
        $middleware->alias([
            'subscription' => CheckCompanySubscription::class,
            'mobile.role' => \App\Http\Middleware\EnsureMobileAppRole::class,
            'mobile.manager' => \App\Http\Middleware\EnsureManagerRole::class,
            'mobile.sales' => \App\Http\Middleware\EnsureSalesAppRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
