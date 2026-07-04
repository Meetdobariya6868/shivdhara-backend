<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Enums\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\BaseRepository;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRoom;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    /** Relations needed to render an order list row. */
    private const LIST_RELATIONS = [
        'customer:id,name,contact',
        'creator:id,name',
        'orderCategory:id,name',
        'orderType:id,name',
    ];

    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Order> $result */
        $result = $this->model->newQuery()
            ->with(self::LIST_RELATIONS)
            ->tap(fn (Builder $query) => $this->applyFilters($query, $filters))
            ->latest()
            ->paginate($perPage);

        return $result;
    }

    /**
     * Apply the optional order-list filters. Every branch targets an indexed
     * column (order_date, order_category_id, order_type_id, creator_id) so the
     * query stays fast at scale; search matches the related customer.
     *
     * @param  Builder<Order>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->whereHas('customer', function (Builder $c) use ($search): void {
                $c->where('name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['date_from'])) {
            $query->where('order_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('order_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['order_category_id'])) {
            $query->where('order_category_id', (int) $filters['order_category_id']);
        }

        if (! empty($filters['order_type_id'])) {
            $query->where('order_type_id', (int) $filters['order_type_id']);
        }

        if (! empty($filters['creator_id'])) {
            $query->where('creator_id', (int) $filters['creator_id']);
        }
    }

    /**
     * @return Collection<int, User>
     */
    public function salesmenWithOrders(): Collection
    {
        /** @var Collection<int, User> $result */
        $result = User::query()
            ->whereHas('orders')
            ->orderBy('name')
            ->get(['id', 'name']);

        return $result;
    }

    public function deleteByCreator(int $creatorId): int
    {
        return $this->model->newQuery()
            ->where('creator_id', $creatorId)
            ->delete();
    }

    /**
     * @return Collection<int, Order>
     */
    public function listByCreator(int $creatorId): Collection
    {
        /** @var Collection<int, Order> $result */
        $result = $this->model->newQuery()
            ->where('creator_id', $creatorId)
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

    public function findDetail(int $id): ?Order
    {
        /** @var Order|null $order */
        $order = $this->model->newQuery()
            ->with([
                'customer:id,name,contact',
                'creator:id,name',
                'orderCategory:id,name',
                'orderType:id,name',
                'rooms' => fn ($q) => $q->orderBy('sort_order'),
                'rooms.items' => fn ($q) => $q->orderBy('sort_order'),
                'rooms.items.designVariant.design.company',
            ])
            ->find($id);

        return $order;
    }

    /**
     * @param  array<string, mixed>  $orderAttributes
     * @param  list<array<string, mixed>>  $roomsData
     */
    public function createGraph(array $orderAttributes, array $roomsData): Order
    {
        /** @var Order $order */
        $order = $this->model->newQuery()->create($orderAttributes);

        foreach ($roomsData as $roomData) {
            /** @var list<array<string, mixed>> $items */
            $items = $roomData['items'] ?? [];
            unset($roomData['items']);

            $room = $order->rooms()->create($roomData);
            $room->items()->createMany($items);
        }

        return $order->load([
            'customer:id,name,contact',
            'creator:id,name',
            'orderCategory:id,name',
            'orderType:id,name',
            'rooms.items.designVariant.design.company',
        ]);
    }

    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $order->status = $status;
        $order->save();

        /** @var Order $detail */
        $detail = $this->findDetail($order->id);

        return $detail;
    }

    public function renameRoom(OrderRoom $room, string $roomName): Order
    {
        $room->room_name = $roomName;
        $room->save();

        /** @var Order $detail */
        $detail = $this->findDetail($room->order_id);

        return $detail;
    }

    public function moveItem(OrderItem $item, int $targetRoomId): Order
    {
        $orderId = $item->room->order_id;

        $item->room_id = $targetRoomId;
        $item->save();

        /** @var Order $detail */
        $detail = $this->findDetail($orderId);

        return $detail;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateItem(OrderItem $item, array $attributes): Order
    {
        $orderId = $item->room->order_id;

        $item->fill($attributes)->save();
        $this->recalcOrderTotal($orderId);

        /** @var Order $detail */
        $detail = $this->findDetail($orderId);

        return $detail;
    }

    public function deleteItem(OrderItem $item): Order
    {
        // Capture the order id before soft-deleting (relation becomes inaccessible).
        $orderId = $item->room->order_id;

        $item->delete();
        $this->recalcOrderTotal($orderId);

        /** @var Order $detail */
        $detail = $this->findDetail($orderId);

        return $detail;
    }

    /**
     * Recompute grand_total from the sum of all non-deleted item totals plus
     * transportation_charge. Called after any item mutation that changes value.
     */
    private function recalcOrderTotal(int $orderId): void
    {
        /** @var Order|null $order */
        $order = $this->model->newQuery()
            ->with('rooms.items')
            ->find($orderId);

        if ($order === null) {
            return;
        }

        $productTotal = $order->rooms
            ->flatMap(fn ($room) => $room->items)
            ->sum('product_total');

        $order->grand_total = round((float) $productTotal + (float) $order->transportation_charge, 2);
        $order->save();
    }
}
