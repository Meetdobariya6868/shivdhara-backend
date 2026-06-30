<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Enums\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\BaseRepository;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRoom;
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
     * @return Collection<int, Order>
     */
    public function listAll(): Collection
    {
        /** @var Collection<int, Order> $result */
        $result = $this->model->newQuery()
            ->with(self::LIST_RELATIONS)
            ->latest()
            ->get();

        return $result;
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
}
