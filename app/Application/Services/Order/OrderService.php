<?php

declare(strict_types=1);

namespace App\Application\Services\Order;

use App\Application\Services\BaseService;
use App\Domain\Contracts\OrderRepositoryInterface;
use App\Models\OrderCategory;
use App\Models\OrderType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Business logic for the Orders module.
 * Only the listing/filtering capability is here for Phase 5 read layer.
 * Write operations (create/update/delete) will be added in a later phase.
 */
final class OrderService extends BaseService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    /**
     * @return Collection<int, \App\Models\Order>
     */
    public function listOrders(): Collection
    {
        return $this->orderRepository->listAll();
    }

    /**
     * @return Collection<int, OrderCategory>
     */
    public function listCategories(): Collection
    {
        /** @var Collection<int, OrderCategory> $result */
        $result = OrderCategory::where('is_active', true)->orderBy('name')->get();

        return $result;
    }

    /**
     * @return Collection<int, OrderType>
     */
    public function listTypes(): Collection
    {
        /** @var Collection<int, OrderType> $result */
        $result = OrderType::where('is_active', true)->orderBy('name')->get();

        return $result;
    }
}
