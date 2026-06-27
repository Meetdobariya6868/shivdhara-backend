<?php

use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Unauthenticated (missing or expired token)
        $exceptions->render(function (AuthenticationException $e, Request $request): ?Response {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please log in to continue.',
                ], Response::HTTP_UNAUTHORIZED);
            }
            return null;
        });

        // Model not found (findOrFail on any Eloquent model)
        $exceptions->render(function (ModelNotFoundException $e, Request $request): ?Response {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found.',
                ], Response::HTTP_NOT_FOUND);
            }
            return null;
        });

        // Route not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request): ?Response {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested endpoint does not exist.',
                ], Response::HTTP_NOT_FOUND);
            }
            return null;
        });

        // Validation failure — override Laravel's default format for consistency
        $exceptions->render(function (ValidationException $e, Request $request): ?Response {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return null;
        });

    })->create();
