<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic repository contract.
 *
 * Defines the persistence operations every concrete repository must expose.
 * Application- and service-layer code depends on this abstraction — never on a
 * concrete Eloquent implementation — which satisfies the Dependency Inversion
 * Principle (the "D" in SOLID) and keeps the domain free of framework details.
 */
interface RepositoryInterface
{
    public function all(): Collection;

    public function find(int|string $id): ?Model;

    public function findOrFail(int|string $id): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int|string $id, array $attributes): Model;

    public function delete(int|string $id): bool;
}
