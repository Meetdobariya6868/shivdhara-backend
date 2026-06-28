<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Contracts\CustomerRepositoryInterface;
use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Contracts\ProductCatalogRepositoryInterface;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Infrastructure\Persistence\Eloquent\Repositories\CustomerRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\OrderRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\ProductCatalogRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\UserRepository;
use App\Listeners\Auth\LogSuccessfulLogin;
use App\Listeners\Auth\LogSuccessfulLogout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(ProductCatalogRepositoryInterface::class, ProductCatalogRepository::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->registerRateLimiters();
        $this->registerEventListeners();
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('auth.login', static function (Request $request): Limit {
            return Limit::perMinute(5)
                ->by($request->input('mobile_number', $request->ip()))
                ->response(static function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many login attempts. Please wait a minute before trying again.',
                    ], 429);
                });
        });
    }

    private function registerEventListeners(): void
    {
        Event::listen(UserLoggedIn::class, LogSuccessfulLogin::class);
        Event::listen(UserLoggedOut::class, LogSuccessfulLogout::class);
    }
}
