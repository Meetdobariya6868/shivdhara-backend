<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Order;

use App\Models\OrderType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderType
 */
final class OrderTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
