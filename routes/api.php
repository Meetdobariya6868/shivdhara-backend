<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Catalog\DesignVariantController;
use App\Http\Controllers\Api\V1\Order\OrderController;
use App\Http\Controllers\Api\V1\Order\OrderItemController;
use App\Http\Controllers\Api\V1\Order\OrderRoomController;
use App\Http\Controllers\Api\V1\Profile\ProfileController;
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
    | Profile (current user, any role — edits only themselves)
    |------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
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
        Route::patch('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions');
        Route::patch('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // A salesman's own orders (admin, or the salesman viewing themselves).
        Route::get('/users/{user}/orders', [OrderController::class, 'byUser'])->name('users.orders');
    });

    /*
    |------------------------------------------------------------------
    | Orders (Phase 5 — read layer)
    |------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        // Admin-only list (authorization enforced via OrderPolicy)
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

        // Salesman filter options (admin) — must precede /orders/{order} so
        // "salesmen" is not matched as an order id.
        Route::get('/orders/salesmen', [OrderController::class, 'salesmen'])->name('orders.salesmen');

        // Single order detail (admin, or the salesman who created it — OrderPolicy@view)
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        // Create order — shared by admin + salesman (authorization via OrderPolicy@create)
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::post('/order-item-images', [OrderController::class, 'uploadItemImage'])->name('orders.item-images.store');

        // Order mutations (authorization via OrderPolicy@update / @delete)
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        Route::patch('/order-rooms/{orderRoom}', [OrderRoomController::class, 'update'])->name('order-rooms.update');
        Route::patch('/order-items/{orderItem}', [OrderItemController::class, 'update'])->name('order-items.update');
        Route::delete('/order-items/{orderItem}', [OrderItemController::class, 'destroy'])->name('order-items.destroy');
        Route::patch('/order-items/{orderItem}/move', [OrderItemController::class, 'move'])->name('order-items.move');

        // Reference data — available to all authenticated users (needed for create order)
        Route::get('/order-categories', [OrderController::class, 'categories'])->name('orders.categories');
        Route::get('/order-types', [OrderController::class, 'types'])->name('orders.types');

        // Catalogue autocomplete for the Add-Item modal (FULLTEXT-backed search).
        Route::get('/design-variants/search', [DesignVariantController::class, 'search'])->name('design-variants.search');
    });

});
