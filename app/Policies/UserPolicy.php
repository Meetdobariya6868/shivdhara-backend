<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Authorization rules for the User Management module.
 *
 * Every action requires the actor to be an admin. Self-mutation guards
 * (an admin may not block or delete their own account) are enforced here
 * so controllers and services stay free of authorization branching.
 */
final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function view(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function update(User $actor): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Block / unblock a salesman. An admin can never change their own status.
     */
    public function changeStatus(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->id !== $target->id;
    }

    /**
     * Soft-delete a salesman. An admin can never delete their own account.
     */
    public function delete(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->id !== $target->id;
    }
}
