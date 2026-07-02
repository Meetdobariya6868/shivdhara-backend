<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;
use App\Models\OrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a partial update to a single order item (quantities, dimensions,
 * rate, image). The product identity (design / catalogue fields) is immutable
 * after creation. Authorization delegates to OrderPolicy@update on the parent order.
 */
final class UpdateOrderItemRequest extends FormRequest
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
        return [
            // Use 'sometimes' so omitting the key leaves the stored image unchanged;
            // sending null explicitly clears it; a string path replaces it.
            'product_image_path' => ['sometimes', 'nullable', 'string', 'max:255'],

            'item_type'        => ['required', Rule::enum(ItemType::class)],
            'measurement_unit' => ['required', Rule::enum(MeasurementUnit::class)],
            'height'           => ['required', 'numeric', 'gt:0'],
            'width'            => ['required', 'numeric', 'gt:0'],
            'sqft_rate'        => ['required', 'numeric', 'min:0'],
            // Per-item price (editable); product_total is derived server-side.
            'price_per_item'   => ['required', 'numeric', 'min:0'],

            // Quantity is required for both types; pieces-per-box for box items only. 0 is allowed.
            'quantity'        => ['required', 'integer', 'min:0'],
            'pieces_per_box'  => ['nullable', 'required_if:item_type,box', 'integer', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'pieces_per_box.required_if' => 'Pieces per box is required for box items.',
            'quantity.required'          => 'Quantity is required.',
        ];
    }
}
