<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Persistence contract for the order aggregate. The filtered, paginated listing
 * is the backbone of the admin Order Management screen (date range, customer,
 * salesman, type, category, status, search).
 */
interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
