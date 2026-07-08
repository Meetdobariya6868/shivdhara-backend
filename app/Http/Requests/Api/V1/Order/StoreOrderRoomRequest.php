<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates adding a room to an order. Authorization is delegated to
 * OrderPolicy@update on the parent order resolved from the route.
 */
final class StoreOrderRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && ($this->user()?->can('update', $order) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'room_name' => ['required', 'string', 'max:80'],
        ];
    }
}
