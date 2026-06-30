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
use App\Models\Order;
use App\Models\OrderCategory;
use App\Models\OrderType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Business logic for the Orders module: listing (read) and creation (write).
 *
 * Order creation find-or-creates the customer and each item's catalogue
 * product, then persists the whole graph atomically. The line total
 * (product_total) is supplied per item and the order grand_total is summed
 * from those plus transportation.
 */
final class OrderService extends BaseService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProductCatalogRepositoryInterface $catalog,
    ) {}

    /**
     * @return Collection<int, Order>
     */
    public function listOrders(): Collection
    {
        return $this->orderRepository->listAll();
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
     * Create a full order (customer + rooms + items) atomically.
     * All totals are derived server-side; client-supplied amounts are ignored.
     */
    public function createOrder(CreateOrderDTO $dto, User $creator): Order
    {
        return DB::transaction(function () use ($dto, $creator): Order {
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

                    $itemsData[] = [
                        'design_variant_id'  => $variant->id,
                        'product_image_path' => $itemDto->productImagePath,
                        'item_type'          => $type->value,
                        'pieces_per_box'     => $type === ItemType::Box ? $itemDto->piecesPerBox : null,
                        'number_of_boxes'    => $type === ItemType::Box ? $itemDto->numberOfBoxes : null,
                        'number_of_pieces'   => $type === ItemType::Piece ? $itemDto->numberOfPieces : null,
                        'measurement_unit'   => $unit->value,
                        'height'             => $itemDto->height,
                        'width'              => $itemDto->width,
                        'sqft_rate'          => $itemDto->sellRate,
                        'product_total'      => $itemDto->productTotal,
                        'sort_order'         => $index,
                    ];

                    $orderProductTotal += $itemDto->productTotal;
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
                    'order_date'            => now()->toDateString(),
                    'customer_id'           => $customer->id,
                    'order_category_id'     => $dto->orderCategoryId,
                    'order_type_id'         => $dto->orderTypeId,
                    'creator_id'            => $creator->id,
                    'advance_payment'       => $dto->advancePayment,
                    'transportation_charge' => $dto->transportationCharge,
                    'notes'                 => $dto->notes,
                    'grand_total'           => $grandTotal,
                ],
                roomsData: $roomsData,
            );
        });
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
