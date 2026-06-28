<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence contract for the order aggregate.
 * All orders are returned in a single query; filtering/search happen client-side.
 * This keeps the API latency flat and eliminates per-keystroke requests.
 */
interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * Return every order with its related customer, creator, category, and type
     * eager-loaded. Ordered newest-first.
     *
     * @return Collection<int, Order>
     */
    public function listAll(): Collection;
}
