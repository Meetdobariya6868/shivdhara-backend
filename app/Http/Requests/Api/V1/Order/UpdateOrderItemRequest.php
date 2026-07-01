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
            'product_total'    => ['required', 'numeric', 'min:0'],

            // Conditional quantity fields — mirror the DB CHECK constraint.
            'pieces_per_box'  => ['nullable', 'required_if:item_type,box',   'integer', 'min:1'],
            'number_of_boxes' => ['nullable', 'required_if:item_type,box',   'integer', 'min:1'],
            'number_of_pieces'=> ['nullable', 'required_if:item_type,piece', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'pieces_per_box.required_if'  => 'Pieces per box is required for box items.',
            'number_of_boxes.required_if' => 'Number of boxes is required for box items.',
            'number_of_pieces.required_if'=> 'Number of pieces is required for piece items.',
        ];
    }
}
