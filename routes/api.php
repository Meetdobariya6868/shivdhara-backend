<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Order\OrderController;
use App\Http\Controllers\Api\V1\User\UserController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api (bootstrap/app.php) + /v1 (below).
| Business modules are added per phase. Auth is Phase 1.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {

    /*
    |------------------------------------------------------------------
    | Health Probe — unauthenticated, no rate limit
    |------------------------------------------------------------------
    */
    Route::get('/health', static fn (): JsonResponse => response()->json([
        'status'  => 'ok',
        'service' => config('app.name'),
        'version' => 'v1',
    ]))->name('health');

    /*
    |------------------------------------------------------------------
    | Authentication
    |------------------------------------------------------------------
    */
    Route::prefix('auth')->name('auth.')->group(function (): void {

        // Public — rate-limited to 5 attempts/min per mobile number
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:auth.login')
            ->name('login');

        // Protected — requires valid Sanctum token + active account
        Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/me', [AuthController::class, 'me'])->name('me');
        });
    });

    /*
    |------------------------------------------------------------------
    | User Management (Admin only — authorization via UserPolicy)
    |------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::patch('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    /*
    |------------------------------------------------------------------
    | Orders (Phase 5 — read layer)
    |------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        // Admin-only list (authorization enforced via OrderPolicy)
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

        // Create order — shared by admin + salesman (authorization via OrderPolicy@create)
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::post('/order-item-images', [OrderController::class, 'uploadItemImage'])->name('orders.item-images.store');

        // Reference data — available to all authenticated users (needed for create order)
        Route::get('/order-categories', [OrderController::class, 'categories'])->name('orders.categories');
        Route::get('/order-types', [OrderController::class, 'types'])->name('orders.types');
    });

});
