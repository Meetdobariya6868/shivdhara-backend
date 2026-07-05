<?php

declare(strict_types=1);

namespace App\Application\Services\Order;

use App\Application\DTOs\Order\CreateOrderDTO;
use App\Application\Services\BaseService;
use App\Domain\Contracts\CustomerRepositoryInterface;
use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Contracts\ProductCatalogRepositoryInterface;
use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;
use App\Domain\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderCategory;
use App\Models\OrderItem;
use App\Models\OrderRoom;
use App\Models\OrderType;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Business logic for the Orders module: listing (read) and creation (write).
 *
 * Order creation find-or-creates the customer and each item's catalogue
 * product, then persists the whole graph atomically. Each line total
 * (product_total) is derived server-side from the per-item price and quantity;
 * the order grand_total is summed from those plus transportation.
 */
final class OrderService extends BaseService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProductCatalogRepositoryInterface $catalog,
    ) {}

    /**
     * A filtered, paginated page of orders for the order list — shared by the
     * admin Home tab and the salesman Home tab.
     *
     * Admins see every order matching the filters. Salesmen are always scoped
     * to their own orders: `creator_id` is forced to the actor's id regardless
     * of what the client sent, so a salesman can never list another user's
     * orders by tampering with the filter.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateOrders(array $filters, int $perPage, User $actor): LengthAwarePaginator
    {
        if (! $actor->isAdmin()) {
            $filters['creator_id'] = $actor->id;
        }

        return $this->orderRepository->paginate($filters, $perPage);
    }

    /**
     * Salesmen (including deleted) who have orders — options for the list's
     * salesman filter.
     *
     * @return Collection<int, User>
     */
    public function salesmenWithOrders(): Collection
    {
        return $this->orderRepository->salesmenWithOrders();
    }

    /**
     * @return Collection<int, OrderCategory>
     */
    public function listCategories(): Collection
    {
        /** @var Collection<int, OrderCategory> $result */
        $result = OrderCategory::where('is_active', true)->orderBy('name')->get();

        return $result;
    }

    /**
     * @return Collection<int, OrderType>
     */
    public function listTypes(): Collection
    {
        /** @var Collection<int, OrderType> $result */
        $result = OrderType::where('is_active', true)->orderBy('name')->get();

        return $result;
    }

    /**
     * Every order created by a salesman, newest-first (for the salesman detail
     * screen).
     *
     * @return Collection<int, Order>
     */
    public function listOrdersForSalesman(int $userId): Collection
    {
        return $this->orderRepository->listByCreator($userId);
    }

    /**
     * A single order with its full room/item graph for the detail screen.
     *
     * @throws ModelNotFoundException when the order does not exist.
     */
    public function getOrderDetail(int $id): Order
    {
        return $this->orderRepository->findDetail($id)
            ?? throw (new ModelNotFoundException())->setModel(Order::class, [$id]);
    }

    /**
     * Create a full order (customer + rooms + items) atomically.
     * All totals are derived server-side; client-supplied amounts are ignored.
     */
    public function createOrder(CreateOrderDTO $dto, User $creator): Order
    {
        return DB::transaction(function () use ($dto, $creator): Order {
            // Architect name is kept only for "Architect"-type orders; for any
            // other type it is forced to null so stray client input is dropped.
            $isArchitectType = OrderType::whereKey($dto->orderTypeId)
                ->whereRaw('LOWER(name) = ?', ['architect'])
                ->exists();
            $architectName = $isArchitectType && $dto->architectName !== null && trim($dto->architectName) !== ''
                ? trim($dto->architectName)
                : null;

            $customer = $this->customerRepository->findByContact(trim($dto->customerContact))
                ?? $this->customerRepository->create([
                    'name'          => trim($dto->customerName),
                    'contact'       => trim($dto->customerContact),
                    'created_by_id' => $creator->id,
                ]);

            $roomsData         = [];
            $orderProductTotal = 0.0;

            foreach ($dto->rooms as $roomDto) {
                $itemsData = [];

                foreach ($roomDto->items as $index => $itemDto) {
                    $type = ItemType::from($itemDto->itemType);
                    $unit = MeasurementUnit::from($itemDto->measurementUnit);

                    // Selected from the autocomplete → link to that exact
                    // variant (no duplicate catalogue rows). Typed-in product
                    // (or a stale/inactive id) → find-or-create by free text.
                    $variant = ($itemDto->designVariantId !== null
                        ? $this->catalog->findVariant($itemDto->designVariantId)
                        : null)
                        ?? $this->catalog->resolveVariant(
                            companyName: $itemDto->companyName,
                            designName: $itemDto->designName,
                            size: $itemDto->size,
                            finish: $itemDto->finish,
                            thickness: $itemDto->thickness,
                            purchaseRate: $itemDto->purchaseRate,
                            sellRate: $itemDto->sellRate,
                        );

                    $piecesPerBox = $type === ItemType::Box ? $itemDto->piecesPerBox : null;
                    $productTotal = $this->computeProductTotal(
                        $type,
                        $itemDto->pricePerItem,
                        $itemDto->quantity,
                        $piecesPerBox,
                    );

                    $itemsData[] = [
                        'design_variant_id'  => $variant->id,
                        'product_image_path' => $itemDto->productImagePath,
                        'item_type'          => $type->value,
                        'quantity'           => $itemDto->quantity,
                        'pieces_per_box'     => $piecesPerBox,
                        'measurement_unit'   => $unit->value,
                        'height'             => $itemDto->height,
                        'width'              => $itemDto->width,
                        'sqft_rate'          => $itemDto->sellRate,
                        'price_per_item'     => $itemDto->pricePerItem,
                        'product_total'      => $productTotal,
                        'sort_order'         => $index,
                    ];

                    $orderProductTotal += $productTotal;
                }

                $roomsData[] = [
                    'room_name'  => $roomDto->roomName,
                    'sort_order' => $roomDto->sortOrder,
                    'items'      => $itemsData,
                ];
            }

            $grandTotal = round($orderProductTotal + $dto->transportationCharge, 2);

            return $this->orderRepository->createGraph(
                orderAttributes: [
                    'customer_id'           => $customer->id,
                    'order_category_id'     => $dto->orderCategoryId,
                    'order_type_id'         => $dto->orderTypeId,
                    'creator_id'            => $creator->id,
                    'status'                => OrderStatus::Pending,
                    'advance_payment'       => $dto->advancePayment,
                    'transportation_charge' => $dto->transportationCharge,
                    'notes'                 => $dto->notes,
                    'architect_name'        => $architectName,
                    'grand_total'           => $grandTotal,
                ],
                roomsData: $roomsData,
            );
        });
    }

    /** Transition an order to a new workflow status. */
    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        return $this->orderRepository->updateStatus($order, $status);
    }

    /** Soft-delete an order (the graph cascades via the rooms/items relations). */
    public function deleteOrder(Order $order): void
    {
        $this->orderRepository->delete($order->id);
    }

    /** Rename a room within an order. */
    public function renameRoom(OrderRoom $room, string $roomName): Order
    {
        return $this->orderRepository->renameRoom($room, trim($roomName));
    }

    /** Move an item to another room belonging to the same order. */
    public function moveItem(OrderItem $item, int $targetRoomId): Order
    {
        return $this->orderRepository->moveItem($item, $targetRoomId);
    }

    /**
     * Update mutable item fields (quantities, dimensions, rate, image).
     * The grand_total of the parent order is recomputed automatically.
     *
     * @param  array<string, mixed>  $data  Validated payload from UpdateOrderItemRequest.
     */
    public function updateItem(OrderItem $item, array $data): Order
    {
        $type         = ItemType::from((string) $data['item_type']);
        $piecesPerBox = $type === ItemType::Box && isset($data['pieces_per_box'])
            ? (int) $data['pieces_per_box']
            : null;

        // Normalise the type-conditional field and derive the line total
        // server-side — never trust a client-supplied product_total.
        $data['pieces_per_box'] = $piecesPerBox;
        $data['product_total']  = $this->computeProductTotal(
            $type,
            (float) $data['price_per_item'],
            (int) $data['quantity'],
            $piecesPerBox,
        );

        return $this->orderRepository->updateItem($item, $data);
    }

    /**
     * Derive an item's line total from its per-item price and quantity.
     * Box items bill every piece across all boxes (pieces_per_box × boxes);
     * piece items bill the piece count directly.
     */
    private function computeProductTotal(
        ItemType $type,
        float $pricePerItem,
        int $quantity,
        ?int $piecesPerBox,
    ): float {
        $totalPieces = $type === ItemType::Box
            ? $quantity * (int) $piecesPerBox
            : $quantity;

        return round($pricePerItem * $totalPieces, 2);
    }

    /**
     * Soft-delete an item. The grand_total of the parent order is recomputed.
     * Returns the updated order detail so the caller can return it to the client.
     */
    public function deleteItem(OrderItem $item): Order
    {
        return $this->orderRepository->deleteItem($item);
    }

    /**
     * Store an uploaded order-item image on the public disk.
     *
     * @return array{path: string, url: string}
     */
    public function storeItemImage(UploadedFile $image): array
    {
        $path = $image->store('order-items', 'public');

        return [
            'path' => $path,
            'url'  => Storage::disk('public')->url($path),
        ];
    }
}
