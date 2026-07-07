<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Application\DTOs\Order\CreateOrderDTO;
use App\Application\Services\Order\OrderService;
use App\Application\Services\Order\QuotationService;
use App\Domain\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\IndexOrderRequest;
use App\Http\Requests\Api\V1\Order\QuotationRequest;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;
use App\Http\Requests\Api\V1\Order\UpdateOrderStatusRequest;
use App\Http\Requests\Api\V1\Order\UploadOrderItemImageRequest;
use App\Http\Resources\Api\V1\Order\OrderCategoryResource;
use App\Http\Resources\Api\V1\Order\OrderResource;
use App\Http\Resources\Api\V1\Order\OrderTypeResource;
use App\Http\Resources\Api\V1\Order\SalesmanOptionResource;
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
        private readonly QuotationService $quotationService,
    ) {}

    /**
     * GET /orders — paginated, server-filtered order list. Shared by the admin
     * and salesman Home tabs: admins see every order, salesmen see only their
     * own (enforced in OrderService@paginateOrders, never trusting the client).
     * Filters (search, date range, category, type, salesman) and pagination are
     * applied in SQL against indexed columns; authorization via IndexOrderRequest.
     */
    public function index(IndexOrderRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 20);

        $paginator = $this->orderService->paginateOrders($filters, $perPage, $request->user());

        return $this->paginated(
            $paginator,
            OrderResource::collection($paginator->getCollection()),
            'Orders retrieved.',
        );
    }

    /**
     * GET /orders/salesmen — salesmen (incl. deleted) who have orders, for the
     * admin order list's salesman filter dropdown. Admin only — deliberately
     * NOT gated by OrderPolicy@viewAny (which now also allows salesmen to list
     * their own orders); exposing the full salesman roster stays admin-only.
     */
    public function salesmen(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isAdmin() ?? false, Response::HTTP_FORBIDDEN);

        return $this->success(
            data: SalesmanOptionResource::collection($this->orderService->salesmenWithOrders()),
            message: 'Salesmen retrieved.',
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
     * GET /orders/{order}/quotation?format=name|code — the order's quotation PDF.
     * "name" prints design names + finish; "code" prints design codes. Streamed
     * inline; the client fetches it as a blob to view or download. Authorization
     * via QuotationRequest (OrderPolicy@view).
     */
    public function quotation(QuotationRequest $request, Order $order): Response
    {
        /** @var 'name'|'code' $format */
        $format = $request->validated('format');

        $detail = $this->orderService->getOrderDetail($order->id);

        return $this->quotationService->render($detail, $format)
            ->stream("quotation-order-{$order->id}-{$format}.pdf");
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
