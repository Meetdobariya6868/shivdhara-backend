<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Application\Services\Order\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\MoveOrderItemRequest;
use App\Http\Resources\Api\V1\Order\OrderResource;
use App\Models\OrderItem;
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
}
