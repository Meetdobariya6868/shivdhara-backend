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
 * Purchase/sell amounts are recomputed server-side from these inputs. The
 * line's product_total is supplied here because it is user-editable in the
 * "Add Detail" popup (auto-filled from the formula, but overridable).
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
        public readonly ?int $piecesPerBox,
        public readonly ?int $numberOfBoxes,
        public readonly ?int $numberOfPieces,
        public readonly string $measurementUnit,  // 'mm' | 'inch' | 'feet'
        public readonly float $height,
        public readonly float $width,
        public readonly float $purchaseRate,
        public readonly float $sellRate,
        public readonly float $productTotal,
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
            piecesPerBox: isset($data['pieces_per_box']) ? (int) $data['pieces_per_box'] : null,
            numberOfBoxes: isset($data['number_of_boxes']) ? (int) $data['number_of_boxes'] : null,
            numberOfPieces: isset($data['number_of_pieces']) ? (int) $data['number_of_pieces'] : null,
            measurementUnit: (string) $data['measurement_unit'],
            height: (float) $data['height'],
            width: (float) $data['width'],
            purchaseRate: (float) $data['purchase_rate'],
            sellRate: (float) $data['sell_rate'],
            productTotal: (float) $data['product_total'],
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
            'pieces_per_box'     => $this->piecesPerBox,
            'number_of_boxes'    => $this->numberOfBoxes,
            'number_of_pieces'   => $this->numberOfPieces,
            'measurement_unit'   => $this->measurementUnit,
            'height'             => $this->height,
            'width'              => $this->width,
            'purchase_rate'      => $this->purchaseRate,
            'sell_rate'          => $this->sellRate,
            'product_total'      => $this->productTotal,
        ];
    }
}
