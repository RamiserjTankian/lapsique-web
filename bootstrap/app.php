<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR
                | \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST
                | \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT
                | \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO
        );
        $middleware->alias([
            'customer.auth' => \App\Http\Middleware\EnsureCustomerAuthenticated::class,
        ]);

        $middleware->web([
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/mailtrap/events',
            'analytics/collect',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
