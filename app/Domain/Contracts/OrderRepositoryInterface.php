<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRoom;
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
