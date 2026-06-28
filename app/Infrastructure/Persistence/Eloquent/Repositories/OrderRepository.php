<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BaseRepository;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * @return Collection<int, Order>
     */
    public function listAll(): Collection
    {
        /** @var Collection<int, Order> $result */
        $result = $this->model->newQuery()
            ->with([
                'customer:id,name,contact',
                'creator:id,name',
                'orderCategory:id,name',
                'orderType:id,name',
            ])
            ->latest()
            ->get();

        return $result;
    }
}
