<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Order;

use App\Models\OrderRoom;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderRoom
 */
final class OrderRoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'room_name'  => $this->room_name,
            'sort_order' => $this->sort_order,
            'items'      => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
