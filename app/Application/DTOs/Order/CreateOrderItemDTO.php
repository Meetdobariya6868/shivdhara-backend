<?php

declare(strict_types=1);

namespace App\Application\DTOs\Order;

use App\Application\DTOs\BaseDTO;

/**
 * Immutable input for a single order item, as typed by the user in the
 * "Add Detail" popup. Product attributes (company/design/size/finish/thickness)
 * are free text — the service find-or-creates the matching catalogue records.
 *
 * When the user picked a row from the autocomplete, $designVariantId carries
 * that exact variant id so the service can link to it directly (no duplicate
 * catalogue rows). It is null when the user typed a brand-new product, in which
 * case the service falls back to the free-text find-or-create.
 *
 * The per-item price (price_per_item) is supplied here because it is
 * user-editable in the "Add Detail" popup (auto-filled from area × sqft_rate,
 * but overridable). The line total (product_total) is derived server-side as
 * price_per_item × quantity (× pieces_per_box for box items).
 */
final class CreateOrderItemDTO extends BaseDTO
{
    public function __construct(
        public readonly ?int $designVariantId,
        public readonly string $companyName,
        public readonly string $designName,
        public readonly string $size,
        public readonly string $finish,
        public readonly string $thickness,
        public readonly ?string $productImagePath,
        public readonly string $itemType,         // 'box' | 'piece'
        public readonly int $quantity,            // boxes (box) or pieces (piece)
        public readonly ?int $piecesPerBox,       // box items only
        public readonly string $measurementUnit,  // 'mm' | 'inch' | 'feet'
        public readonly float $height,
        public readonly float $width,
        public readonly float $purchaseRate,
        public readonly float $sellRate,
        public readonly float $pricePerItem,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            designVariantId: isset($data['design_variant_id']) ? (int) $data['design_variant_id'] : null,
            companyName: (string) $data['company_name'],
            designName: (string) $data['design_name'],
            size: (string) $data['size'],
            finish: (string) $data['finish'],
            thickness: (string) $data['thickness'],
            productImagePath: isset($data['product_image_path']) ? (string) $data['product_image_path'] : null,
            itemType: (string) $data['item_type'],
            quantity: (int) $data['quantity'],
            piecesPerBox: isset($data['pieces_per_box']) ? (int) $data['pieces_per_box'] : null,
            measurementUnit: (string) $data['measurement_unit'],
            height: (float) $data['height'],
            width: (float) $data['width'],
            purchaseRate: (float) $data['purchase_rate'],
            sellRate: (float) $data['sell_rate'],
            pricePerItem: (float) $data['price_per_item'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'design_variant_id'  => $this->designVariantId,
            'company_name'       => $this->companyName,
            'design_name'        => $this->designName,
            'size'               => $this->size,
            'finish'             => $this->finish,
            'thickness'          => $this->thickness,
            'product_image_path' => $this->productImagePath,
            'item_type'          => $this->itemType,
            'quantity'           => $this->quantity,
            'pieces_per_box'     => $this->piecesPerBox,
            'measurement_unit'   => $this->measurementUnit,
            'height'             => $this->height,
            'width'              => $this->width,
            'purchase_rate'      => $this->purchaseRate,
            'sell_rate'          => $this->sellRate,
            'price_per_item'     => $this->pricePerItem,
        ];
    }
}
