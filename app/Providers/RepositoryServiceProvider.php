<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Contracts\CustomerRepositoryInterface;
use App\Domain\Contracts\DesignRepositoryInterface;
use App\Domain\Contracts\DesignVariantRepositoryInterface;
use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\CustomerRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\DesignRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\DesignVariantRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\OrderRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository contracts to their concrete Eloquent implementations.
 *
 * As business modules are added, register their bindings in the $repositories
 * map below (contract => implementation). Controllers and services then
 * type-hint the contract and Laravel's service container injects the concrete
 * implementation, keeping the codebase aligned with the Dependency Inversion
 * Principle. No bindings exist yet — business modules arrive in later phases.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Contract-to-implementation bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $repositories = [
        UserRepositoryInterface::class => UserRepository::class,
        CustomerRepositoryInterface::class => CustomerRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
        DesignRepositoryInterface::class => DesignRepository::class,
        DesignVariantRepositoryInterface::class => DesignVariantRepository::class,
    ];

    /**
     * Register repository bindings in the container.
     */
    public function register(): void
    {
        foreach ($this->repositories as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }
}
