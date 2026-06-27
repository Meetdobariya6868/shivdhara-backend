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
            // Eager-load to avoid N+1 on the listing screen.
            ->with(['customer', 'creator', 'architect', 'category'])
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('order_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('order_date', '<=', $v))
            ->when($filters['customer_id'] ?? null, fn ($q, $v) => $q->where('customer_id', $v))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['architect_id'] ?? null, fn ($q, $v) => $q->where('architect_id', $v))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['order_type'] ?? null, fn ($q, $v) => $q->where('order_type', $v))
            ->when(isset($filters['order_status']), fn ($q) => $q->where('order_status', $filters['order_status']))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('order_number', 'like', "%{$v}%"))
            ->latest()
            ->paginate($perPage);
    }
}
