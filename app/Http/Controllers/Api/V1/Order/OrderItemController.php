<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Application\DTOs\Order\CreateOrderItemDTO;
use App\Application\Services\Order\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\MoveOrderItemRequest;
use App\Http\Requests\Api\V1\Order\StoreOrderItemRequest;
use App\Http\Requests\Api\V1\Order\UpdateOrderItemRequest;
use App\Http\Resources\Api\V1\Order\OrderResource;
use App\Models\OrderItem;
use App\Models\OrderRoom;
use Illuminate\Http\JsonResponse;

/**
 * Item-level edits within an order. Thin orchestration only — authorization and
 * the same-order constraint are enforced in the FormRequest.
 */
final class OrderItemController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * POST /order-rooms/{orderRoom}/items — add a new item to an existing room.
     * Returns the reloaded parent order detail.
     */
    public function store(StoreOrderItemRequest $request, OrderRoom $orderRoom): JsonResponse
    {
        $order = $this->orderService->addItem(
            $orderRoom,
            CreateOrderItemDTO::fromArray($request->validated()),
        );

        return $this->success(
            data: OrderResource::make($order),
            message: 'Item added.',
        );
    }

    /**
     * PATCH /order-items/{orderItem} — update mutable item fields (quantities,
     * dimensions, rate, image). Returns the reloaded parent order detail.
     */
    public function update(UpdateOrderItemRequest $request, OrderItem $orderItem): JsonResponse
    {
        $order = $this->orderService->updateItem($orderItem, $request->validated());

        return $this->success(
            data: OrderResource::make($order),
            message: 'Item updated.',
        );
    }

    /**
     * PATCH /order-items/{orderItem}/move — move an item to another room of the
     * same order. Returns the reloaded parent order detail.
     */
    public function move(MoveOrderItemRequest $request, OrderItem $orderItem): JsonResponse
    {
        $order = $this->orderService->moveItem($orderItem, (int) $request->validated('room_id'));

        return $this->success(
            data: OrderResource::make($order),
            message: 'Item moved.',
        );
    }

    /**
     * DELETE /order-items/{orderItem} — soft-delete an item and recompute the
     * parent order total. Returns the updated order detail for cache sync.
     */
    public function destroy(OrderItem $orderItem): JsonResponse
    {
        $this->authorize('update', $orderItem->room->order);

        $order = $this->orderService->deleteItem($orderItem);

        return $this->success(
            data: OrderResource::make($order),
            message: 'Item deleted.',
        );
    }
}
