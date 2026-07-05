<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

/**
 * Authorization rules for Order access.
 * Admins see all orders; salesmen are restricted to their own.
 */
final class OrderPolicy
{
    /**
     * Any authenticated user may call the order list — admins see every order,
     * salesmen see only their own. The result set is scoped in
     * OrderService@paginateOrders, mirroring how @view already scopes a single
     * order by creator for salesmen.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Admins can view any order; salesmen only their own. */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->creator_id === $user->id;
    }

    /** Admins, and salesmen explicitly granted the flag, may create orders. */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->can_create_orders;
    }

    /**
     * Admins can edit any order; salesmen only their own. Covers status changes,
     * room renames and moving items between rooms.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->creator_id === $user->id;
    }

    /** Admins can delete any order; salesmen only their own. */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->creator_id === $user->id;
    }
}
