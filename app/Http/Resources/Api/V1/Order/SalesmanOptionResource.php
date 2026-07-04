<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Order;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Minimal salesman projection (id + name) for the orders "salesman" filter
 * dropdown. Kept lean deliberately — the full UserResource is unnecessary here.
 *
 * @mixin User
 */
final class SalesmanOptionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
