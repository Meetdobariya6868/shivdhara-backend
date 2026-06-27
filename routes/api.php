<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Loaded by bootstrap/app.php and automatically prefixed with "/api". Routes
| are versioned ("/api/v1/...") so the contract can evolve without breaking
| existing clients. Business endpoints arrive in later phases; for now we
| expose only an unauthenticated, dependency-free health probe.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/health', static fn (): JsonResponse => response()->json([
        'status' => 'ok',
        'service' => config('app.name'),
        'version' => 'v1',
    ]))->name('health');
});
