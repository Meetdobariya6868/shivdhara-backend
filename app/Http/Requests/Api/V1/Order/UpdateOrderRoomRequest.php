<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Models\OrderRoom;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates renaming a room. Authorization is delegated to OrderPolicy@update
 * on the room's parent order.
 */
final class UpdateOrderRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $room = $this->route('orderRoom');

        return $room instanceof OrderRoom
            && ($this->user()?->can('update', $room->order) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'room_name' => ['required', 'string', 'max:80'],
        ];
    }
}
