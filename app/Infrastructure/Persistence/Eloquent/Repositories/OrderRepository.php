<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BaseRepository;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

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

    /**
     * @param  array<string, mixed>  $orderAttributes
     * @param  list<array<string, mixed>>  $roomsData
     */
    public function createGraph(array $orderAttributes, array $roomsData): Order
    {
        // A temporary unique number satisfies the NOT NULL/unique column until
        // the real id-based number is known.
        $orderAttributes['order_number'] = 'TMP-'.Str::uuid()->toString();

        /** @var Order $order */
        $order = $this->model->newQuery()->create($orderAttributes);

        $order->order_number = \sprintf('SD%s-%05d', $order->order_date->format('Ym'), $order->id);
        $order->save();

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
}
