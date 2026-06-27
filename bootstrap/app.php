<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        // Enable Sanctum's SPA (cookie-based) authentication for the API group.
        // Requests originating from SANCTUM_STATEFUL_DOMAINS are treated as
        // first-party and authenticated via the session; all other requests
        // fall back to bearer-token authentication. No auth is enforced yet —
        // guarded routes are introduced in a later phase.
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
