<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Order;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
final class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'order_date' => $this->order_date->format('Y-m-d'),
            'customer'              => [
                'id'      => $this->customer->id,
                'name'    => $this->customer->name,
                'contact' => $this->customer->contact,
            ],
            'creator'               => [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ],
            'category'              => [
                'id'   => $this->orderCategory->id,
                'name' => $this->orderCategory->name,
            ],
            'type'                  => [
                'id'   => $this->orderType->id,
                'name' => $this->orderType->name,
            ],
            'advance_payment'       => $this->advance_payment,
            'transportation_charge' => $this->transportation_charge,
            'grand_total'           => $this->grand_total,
            'notes'                 => $this->notes,
            'rooms'                 => OrderRoomResource::collection($this->whenLoaded('rooms')),
            'created_at'            => $this->created_at?->toISOString(),
        ];
    }
}
