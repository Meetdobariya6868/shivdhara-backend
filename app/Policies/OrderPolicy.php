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
    /** Full list view is admin-only. */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
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
}
