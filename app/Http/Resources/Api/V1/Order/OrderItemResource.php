<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Order;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin OrderItem
 */
final class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variant = $this->designVariant;
        $design  = $variant?->design;

        return [
            'id'                 => $this->id,
            'product' => [
                'company_name' => $design?->company?->company_name,
                'design_name'  => $design?->design_name,
                'size'         => $variant?->size,
                'finish'       => $variant?->finish,
                'thickness'    => $variant?->thickness,
            ],
            'product_image_url'  => $this->product_image_path
                ? Storage::disk('public')->url($this->product_image_path)
                : null,
            'item_type'          => $this->item_type->value,
            'quantity'           => $this->quantity,
            'pieces_per_box'     => $this->pieces_per_box,
            'measurement_unit'   => $this->measurement_unit->value,
            'height'             => $this->height,
            'width'              => $this->width,
            'sqft_rate'          => $this->sqft_rate,
            'price_per_item'     => $this->price_per_item,
            'product_total'      => $this->product_total,
        ];
    }
}
