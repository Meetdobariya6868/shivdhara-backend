<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Application\DTOs\Order\CreateOrderDTO;
use App\Application\Services\Order\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;
use App\Http\Requests\Api\V1\Order\UploadOrderItemImageRequest;
use App\Http\Resources\Api\V1\Order\OrderCategoryResource;
use App\Http\Resources\Api\V1\Order\OrderResource;
use App\Http\Resources\Api\V1\Order\OrderTypeResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Order endpoints for the admin Orders screen and the shared Create Order flow.
 * Thin orchestration only — business logic lives in OrderService, authorization
 * in OrderPolicy.
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
     * POST /orders — create a full order (customer + rooms + items).
     * Shared by admin and salesman; authorization via OrderPolicy@create
     * (enforced in StoreOrderRequest).
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            CreateOrderDTO::fromArray($request->validated()),
            $request->user(),
        );

        return $this->success(
            data: OrderResource::make($order),
            message: 'Order created successfully.',
            status: Response::HTTP_CREATED,
        );
    }

    /**
     * POST /order-item-images — upload a single product photo, returning its
     * stored path + public URL for inclusion in the order payload.
     */
    public function uploadItemImage(UploadOrderItemImageRequest $request): JsonResponse
    {
        return $this->success(
            data: $this->orderService->storeItemImage($request->file('image')),
            message: 'Image uploaded.',
            status: Response::HTTP_CREATED,
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
