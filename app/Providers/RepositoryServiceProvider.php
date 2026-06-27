<?php

declare(strict_types=1);

namespace App\Providers;

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
        // App\Domain\Contracts\ExampleRepositoryInterface::class
        //     => App\Infrastructure\Persistence\Eloquent\ExampleRepository::class,
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
