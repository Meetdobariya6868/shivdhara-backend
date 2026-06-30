<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Domain\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a workflow status change on an order (e.g. Confirm Order).
 * Authorization is delegated to OrderPolicy@update.
 */
final class UpdateOrderStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ];
    }
}
