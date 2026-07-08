<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Application\Services\Order\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\StoreOrderRoomRequest;
use App\Http\Requests\Api\V1\Order\UpdateOrderRoomRequest;
use App\Http\Resources\Api\V1\Order\OrderResource;
use App\Models\Order;
use App\Models\OrderRoom;
use Illuminate\Http\JsonResponse;

/**
 * Room-level edits within an order. Thin orchestration only — authorization is
 * enforced in the FormRequest against the parent order's update policy.
 */
final class OrderRoomController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * POST /orders/{order}/rooms — add a new, empty room to an order.
     * Returns the reloaded parent order detail.
     */
    public function store(StoreOrderRoomRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderService->addRoom($order, $request->validated('room_name'));

        return $this->success(
            data: OrderResource::make($order),
            message: 'Room added.',
        );
    }

    /**
     * PATCH /order-rooms/{orderRoom} — rename a room.
     * Returns the reloaded parent order detail.
     */
    public function update(UpdateOrderRoomRequest $request, OrderRoom $orderRoom): JsonResponse
    {
        $order = $this->orderService->renameRoom($orderRoom, $request->validated('room_name'));

        return $this->success(
            data: OrderResource::make($order),
            message: 'Room renamed.',
        );
    }

    /**
     * DELETE /order-rooms/{orderRoom} — delete an empty room. Refuses (409)
     * if the room still has items. Returns the reloaded parent order detail.
     */
    public function destroy(OrderRoom $orderRoom): JsonResponse
    {
        $this->authorize('update', $orderRoom->order);

        $order = $this->orderService->deleteRoom($orderRoom);

        return $this->success(
            data: OrderResource::make($order),
            message: 'Room deleted.',
        );
    }
}
