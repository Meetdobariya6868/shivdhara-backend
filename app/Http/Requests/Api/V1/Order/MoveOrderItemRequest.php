<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Models\OrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates moving an item to another room. The target room must belong to the
 * SAME order as the item (prevents moving items across orders). Authorization
 * is delegated to OrderPolicy@update on the item's parent order.
 */
final class MoveOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('orderItem');

        return $item instanceof OrderItem
            && ($this->user()?->can('update', $item->room->order) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var OrderItem $item */
        $item = $this->route('orderItem');

        return [
            'room_id' => [
                'required',
                'integer',
                Rule::exists('order_rooms', 'id')->where('order_id', $item->room->order_id),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'room_id.exists' => 'The target room must belong to the same order.',
        ];
    }
}
