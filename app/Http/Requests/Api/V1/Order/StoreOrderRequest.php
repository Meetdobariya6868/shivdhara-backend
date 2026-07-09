<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;
use App\Models\Order;
use App\Models\OrderType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creation of a full order graph (order → rooms → items).
 * Authorization is delegated to OrderPolicy@create.
 *
 * Quantity is required for every item (boxes for box items, pieces for piece
 * items). Pieces-per-box is required for box items only, mirroring the DB
 * CHECK constraint on order_items.
 */
final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    /**
     * A salesman only fails this gate when they lack the create-orders
     * permission — surface a specific message instead of the generic 403.
     */
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException("You don't have permission to create orders.");
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

            // Required only for "Architect" order types (enforced in withValidator,
            // since the type is identified by name rather than a fixed id).
            'architect_name'        => ['nullable', 'string', 'max:120'],

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
            // The per-order charged rate, stored as order_items.sqft_rate.
            'rooms.*.items.*.sqft_rate'          => ['required', 'numeric', 'min:0'],
            // Per-item price (editable in the UI); product_total is derived server-side.
            'rooms.*.items.*.price_per_item'     => ['required', 'numeric', 'min:0'],

            // Quantity is required for both types (boxes for box items, pieces for piece items); 0 is allowed.
            'rooms.*.items.*.quantity'        => ['required', 'integer', 'min:0'],

            // Pieces-per-box applies to box items only (nulled server-side for pieces); 0 is allowed.
            'rooms.*.items.*.pieces_per_box'  => ['nullable', 'required_if:rooms.*.items.*.item_type,box', 'integer', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'rooms.required'         => 'Add at least one room before saving the order.',
            'rooms.*.items.required' => 'Each room must contain at least one product.',
            'rooms.*.items.*.pieces_per_box.required_if' => 'Pieces per box is required for box items.',
            'rooms.*.items.*.quantity.required'          => 'Quantity is required for every item.',
        ];
    }

    /**
     * Architect orders must carry an architect name. The "Architect" type is
     * matched by name (case-insensitively) because order types have no stable
     * slug/code — only a unique name.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $typeId = $this->input('order_type_id');

            if ($typeId === null || ! $this->orderTypeIsArchitect((int) $typeId)) {
                return;
            }

            if (trim((string) $this->input('architect_name')) === '') {
                $validator->errors()->add(
                    'architect_name',
                    'Architect name is required for architect orders.',
                );
            }
        });
    }

    private function orderTypeIsArchitect(int $orderTypeId): bool
    {
        return OrderType::whereKey($orderTypeId)
            ->whereRaw('LOWER(name) = ?', ['architect'])
            ->exists();
    }
}
