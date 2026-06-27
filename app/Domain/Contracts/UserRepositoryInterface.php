<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\User;

/**
 * Persistence contract for users (admins + salesmen). Extends the generic CRUD
 * contract with the lookups the auth/salesman-management use cases need.
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /** Resolve a user by their unique login phone number. */
    public function findByPhone(string $phone): ?User;
}
