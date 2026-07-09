<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;
use App\Models\OrderRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates adding a new item to an existing room. Mirrors StoreOrderRequest's
 * per-item rules (flat here, since a single item is posted rather than a full
 * order graph). Authorization is delegated to OrderPolicy@update on the room's
 * parent order.
 */
final class StoreOrderItemRequest extends FormRequest
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
            // Set when the user picked a row from the catalogue autocomplete;
            // links the item to that exact variant. Absent for typed-in products.
            'design_variant_id'  => ['nullable', 'integer', Rule::exists('design_variants', 'id')->where('is_active', true)],

            'company_name'       => ['required', 'string', 'max:120'],
            'design_name'        => ['required', 'string', 'max:120'],
            'size'               => ['required', 'string', 'max:40'],
            'finish'             => ['required', 'string', 'max:60'],
            'thickness'          => ['required', 'string', 'max:40'],
            'product_image_path' => ['nullable', 'string', 'max:255'],

            'item_type'          => ['required', Rule::enum(ItemType::class)],
            'measurement_unit'   => ['required', Rule::enum(MeasurementUnit::class)],
            'height'             => ['required', 'numeric', 'gt:0'],
            'width'              => ['required', 'numeric', 'gt:0'],
            'purchase_rate'      => ['required', 'numeric', 'min:0'],
            'sell_rate'          => ['required', 'numeric', 'min:0'],
            // The per-order charged rate, stored as order_items.sqft_rate.
            'sqft_rate'          => ['required', 'numeric', 'min:0'],
            // Per-item price (editable in the UI); product_total is derived server-side.
            'price_per_item'     => ['required', 'numeric', 'min:0'],

            // Quantity is required for both types (boxes for box items, pieces for piece items); 0 is allowed.
            'quantity'           => ['required', 'integer', 'min:0'],

            // Pieces-per-box applies to box items only (nulled server-side for pieces); 0 is allowed.
            'pieces_per_box'     => ['nullable', 'required_if:item_type,box', 'integer', 'min:0'],
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
