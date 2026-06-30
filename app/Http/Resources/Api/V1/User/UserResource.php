<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'mobile_number'     => $this->mobile_number,
            'role'              => $this->role->value,
            'role_label'        => $this->role->label(),
            'status'            => $this->status->value,
            'status_label'      => $this->status->label(),
            'can_create_orders' => $this->can_create_orders,
            'is_admin'          => $this->isAdmin(),
            // Present only when the orders relation count was loaded (user detail).
            'orders_count'      => $this->whenCounted('orders'),
            'created_at'        => $this->created_at?->toISOString(),
        ];
    }
}
