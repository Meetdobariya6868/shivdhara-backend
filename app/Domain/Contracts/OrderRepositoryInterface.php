<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRoom;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence contract for the order aggregate.
 * The admin list is paginated and filtered server-side (indexed columns), so
 * the payload stays small and the query scales to large order volumes.
 */
interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * A page of orders matching the given filters, newest-first, with the
     * customer/creator/category/type relations eager-loaded for each row.
     *
     * Supported filter keys (all optional): search (customer name/contact),
     * date_from, date_to, order_category_id, order_type_id, creator_id.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    /**
     * Distinct active (non-deleted) salesmen who have created at least one
     * order — the option set for the orders "salesman" filter. Ordered by name.
     * Deleted salesmen are excluded: deleting a salesman also deletes their
     * orders, so they can never be a meaningful filter option.
     *
     * @return Collection<int, User>
     */
    public function salesmenWithOrders(): Collection;

    /**
     * Soft-delete every order created by the given salesman. Used when a
     * salesman is deleted so nothing references a removed account. Executed as
     * a single bulk update — no models are hydrated.
     *
     * @return int  Number of orders soft-deleted.
     */
    public function deleteByCreator(int $creatorId): int;

    /**
     * Every order created by the given salesman, newest-first, with the
     * relations needed for an order row (customer, category, type).
     *
     * @return Collection<int, Order>
     */
    public function listByCreator(int $creatorId): Collection;

    /**
     * Fetch a single order with its full graph (rooms → items → catalogue) plus
     * the header relations, ordered by sort_order. Null when not found.
     */
    public function findDetail(int $id): ?Order;

    /**
     * Persist a full order graph (order → rooms → items) in one place.
     * The caller is responsible for wrapping this in a transaction.
     *
     * @param  array<string, mixed>  $orderAttributes  Fully-resolved order columns.
     * @param  list<array<string, mixed>>  $roomsData    Each room's columns plus an `items` list of item columns.
     * @return Order  Reloaded with rooms.items.designVariant.design.company and order relations.
     */
    public function createGraph(array $orderAttributes, array $roomsData): Order;

    /** Set the workflow status of an order and return the reloaded detail graph. */
    public function updateStatus(Order $order, OrderStatus $status): Order;

    /** Rename a room and return the reloaded parent order detail graph. */
    public function renameRoom(OrderRoom $room, string $roomName): Order;

    /** Move an item to another room (same order) and return the reloaded order detail graph. */
    public function moveItem(OrderItem $item, int $targetRoomId): Order;

    /**
     * Overwrite mutable item fields and return the reloaded order detail graph.
     * Also recomputes the parent order's grand_total.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateItem(OrderItem $item, array $attributes): Order;

    /**
     * Soft-delete an item and return the reloaded order detail graph.
     * Also recomputes the parent order's grand_total.
     */
    public function deleteItem(OrderItem $item): Order;
}
