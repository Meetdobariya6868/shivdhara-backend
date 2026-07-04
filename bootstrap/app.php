<?php

use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Token-based API authentication (Bearer tokens via Authorization
        // header). We intentionally do NOT enable statefulApi() / cookie-based
        // SPA auth — that would enforce CSRF verification (causing 419 errors)
        // and is incompatible with the localStorage bearer-token flow the SPA uses.

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

        // Authorization failure (policy / gate denial). Surface a specific
        // message when the denial provided one (e.g. StoreOrderRequest); fall
        // back to the generic message for the framework default so bool-return
        // policies keep behaving exactly as before.
        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request): ?Response {
            if ($request->expectsJson() || $request->is('api/*')) {
                $message = $e->getMessage();
                $isGeneric = $message === '' || $message === 'This action is unauthorized.';

                return response()->json([
                    'success' => false,
                    'message' => $isGeneric ? 'You are not authorized to perform this action.' : $message,
                ], Response::HTTP_FORBIDDEN);
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

        // Catch-all for any other exception on API requests. Runs only when no
        // handler above matched (e.g. a QueryException). Guarantees the client
        // always receives a clean, structured message and NEVER raw SQL or a
        // stack trace. The framework still logs the full exception server-side;
        // real detail is exposed only under a separate `debug` key in local.
        $exceptions->render(function (\Throwable $e, Request $request): ?Response {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            // Preserve intentional HTTP exceptions (404, 405, 429, …) and their codes.
            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'The request could not be processed.',
                ], $e->getStatusCode());
            }

            $payload = [
                'success' => false,
                'message' => 'Something went wrong on our end. Please try again.',
            ];

            if (config('app.debug')) {
                $payload['debug'] = [
                    'exception' => $e::class,
                    'message'   => $e->getMessage(),
                ];
            }

            return response()->json($payload, Response::HTTP_INTERNAL_SERVER_ERROR);
        });

    })->create();
