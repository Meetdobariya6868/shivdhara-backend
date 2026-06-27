<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByMobile(string $mobile): ?User;

    /**
     * Return all salesman accounts with optional status filter.
     * Search/filtering is handled client-side; status filter is cheap server-side.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, User>
     */
    public function listSalesmen(array $filters): Collection;

    /**
     * Determine whether a mobile number is already taken, optionally
     * ignoring a given user id (used when updating an existing user).
     */
    public function mobileExists(string $mobile, ?int $ignoreId = null): bool;
}
