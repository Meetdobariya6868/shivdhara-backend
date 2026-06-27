<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BaseRepository;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['customer', 'creator', 'orderCategory', 'orderType'])
            ->when($filters['date_from'] ?? null,         fn ($q, $v) => $q->whereDate('order_date', '>=', $v))
            ->when($filters['date_to'] ?? null,           fn ($q, $v) => $q->whereDate('order_date', '<=', $v))
            ->when($filters['customer_id'] ?? null,       fn ($q, $v) => $q->where('customer_id', $v))
            ->when($filters['creator_id'] ?? null,        fn ($q, $v) => $q->where('creator_id', $v))
            ->when($filters['order_category_id'] ?? null, fn ($q, $v) => $q->where('order_category_id', $v))
            ->when($filters['order_type_id'] ?? null,     fn ($q, $v) => $q->where('order_type_id', $v))
            ->when($filters['search'] ?? null,            fn ($q, $v) => $q->where('order_number', 'like', "%{$v}%"))
            ->latest()
            ->paginate($perPage);
    }
}
