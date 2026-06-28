<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Application\Services\Order\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Order\OrderCategoryResource;
use App\Http\Resources\Api\V1\Order\OrderResource;
use App\Http\Resources\Api\V1\Order\OrderTypeResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

/**
 * Read-only Order endpoints for the admin Orders screen.
 * Write operations (create, update, delete) will be added when the
 * Create Order flow is built (Phase 5 write layer).
 */
final class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * GET /orders — full order list (admin only).
     * No pagination or server-side filtering; all filtering is client-side.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        return $this->success(
            data: OrderResource::collection($this->orderService->listOrders()),
            message: 'Orders retrieved.',
        );
    }

    /**
     * GET /order-categories — active category list for filter dropdowns.
     * Open to any authenticated user (salesmen need this for create order flow).
     */
    public function categories(): JsonResponse
    {
        return $this->success(
            data: OrderCategoryResource::collection($this->orderService->listCategories()),
            message: 'Categories retrieved.',
        );
    }

    /**
     * GET /order-types — active type list for filter dropdowns.
     */
    public function types(): JsonResponse
    {
        return $this->success(
            data: OrderTypeResource::collection($this->orderService->listTypes()),
            message: 'Order types retrieved.',
        );
    }
}
