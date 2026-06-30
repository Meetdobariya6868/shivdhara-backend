<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Application\DTOs\Order\CreateOrderDTO;
use App\Application\Services\Order\OrderService;
use App\Domain\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;
use App\Http\Requests\Api\V1\Order\UpdateOrderStatusRequest;
use App\Http\Requests\Api\V1\Order\UploadOrderItemImageRequest;
use App\Http\Resources\Api\V1\Order\OrderCategoryResource;
use App\Http\Resources\Api\V1\Order\OrderResource;
use App\Http\Resources\Api\V1\Order\OrderTypeResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * GET /users/{user}/orders — orders created by a salesman (admin, or the
     * salesman viewing their own). Powers the salesman detail screen.
     */
    public function byUser(Request $request, User $user): JsonResponse
    {
        abort_unless(
            $request->user()->isAdmin() || $request->user()->id === $user->id,
            Response::HTTP_FORBIDDEN,
        );

        return $this->success(
            data: OrderResource::collection($this->orderService->listOrdersForSalesman($user->id)),
            message: 'Salesman orders retrieved.',
        );
    }

    /**
     * GET /orders/{order} — full order detail (header + rooms + items).
     * Admins see any order; salesmen only their own (OrderPolicy@view).
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return $this->success(
            data: OrderResource::make($this->orderService->getOrderDetail($order->id)),
            message: 'Order retrieved.',
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

    /**
     * PATCH /orders/{order}/status — change workflow status (e.g. confirm).
     * Authorization via OrderPolicy@update (enforced in UpdateOrderStatusRequest).
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $updated = $this->orderService->updateStatus(
            $order,
            OrderStatus::from($request->validated('status')),
        );

        return $this->success(
            data: OrderResource::make($updated),
            message: 'Order status updated.',
        );
    }

    /**
     * DELETE /orders/{order} — soft-delete an order.
     * Admins delete any order; salesmen only their own (OrderPolicy@delete).
     */
    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('delete', $order);

        $this->orderService->deleteOrder($order);

        return $this->message('Order deleted successfully.');
    }
}
