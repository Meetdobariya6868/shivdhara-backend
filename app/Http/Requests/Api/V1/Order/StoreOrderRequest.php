<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creation of a full order graph (order → rooms → items).
 * Authorization is delegated to OrderPolicy@create.
 *
 * Conditional quantity fields mirror the DB CHECK constraint on order_items:
 *   box   → pieces_per_box + number_of_boxes required
 *   piece → number_of_pieces required
 */
final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'customer_name'         => ['required', 'string', 'max:120'],
            'customer_contact'      => ['required', 'string', 'max:15'],
            'order_category_id'     => ['required', 'integer', Rule::exists('order_categories', 'id')->where('is_active', true)],
            'order_type_id'         => ['required', 'integer', Rule::exists('order_types', 'id')->where('is_active', true)],
            'advance_payment'       => ['required', 'numeric', 'min:0'],
            'transportation_charge' => ['required', 'numeric', 'min:0'],
            'notes'                 => ['nullable', 'string', 'max:2000'],

            'rooms'                 => ['required', 'array', 'min:1'],
            'rooms.*.room_name'     => ['required', 'string', 'max:80'],
            'rooms.*.sort_order'    => ['required', 'integer', 'min:0'],
            'rooms.*.items'         => ['required', 'array', 'min:1'],

            // Set when the user picked a row from the catalogue autocomplete;
            // links the item to that exact variant. Absent for typed-in products.
            'rooms.*.items.*.design_variant_id'  => ['nullable', 'integer', Rule::exists('design_variants', 'id')->where('is_active', true)],

            'rooms.*.items.*.company_name'       => ['required', 'string', 'max:120'],
            'rooms.*.items.*.design_name'        => ['required', 'string', 'max:120'],
            'rooms.*.items.*.size'               => ['required', 'string', 'max:40'],
            'rooms.*.items.*.finish'             => ['required', 'string', 'max:60'],
            'rooms.*.items.*.thickness'          => ['required', 'string', 'max:40'],
            'rooms.*.items.*.product_image_path' => ['nullable', 'string', 'max:255'],

            'rooms.*.items.*.item_type'          => ['required', Rule::enum(ItemType::class)],
            'rooms.*.items.*.measurement_unit'   => ['required', Rule::enum(MeasurementUnit::class)],
            'rooms.*.items.*.height'             => ['required', 'numeric', 'gt:0'],
            'rooms.*.items.*.width'              => ['required', 'numeric', 'gt:0'],
            'rooms.*.items.*.purchase_rate'      => ['required', 'numeric', 'min:0'],
            'rooms.*.items.*.sell_rate'          => ['required', 'numeric', 'min:0'],
            'rooms.*.items.*.product_total'      => ['required', 'numeric', 'min:0'],

            // Box-only quantities
            'rooms.*.items.*.pieces_per_box'  => ['nullable', 'required_if:rooms.*.items.*.item_type,box', 'integer', 'min:1'],
            'rooms.*.items.*.number_of_boxes' => ['nullable', 'required_if:rooms.*.items.*.item_type,box', 'integer', 'min:1'],

            // Piece-only quantity
            'rooms.*.items.*.number_of_pieces' => ['nullable', 'required_if:rooms.*.items.*.item_type,piece', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'rooms.required'         => 'Add at least one room before saving the order.',
            'rooms.*.items.required' => 'Each room must contain at least one product.',
            'rooms.*.items.*.pieces_per_box.required_if'   => 'Pieces per box is required for box items.',
            'rooms.*.items.*.number_of_boxes.required_if'  => 'Number of boxes is required for box items.',
            'rooms.*.items.*.number_of_pieces.required_if' => 'Number of pieces is required for piece items.',
        ];
    }
}
