<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates editing an existing order's header fields (customer name/contact,
 * category, type, order date) from the order detail screen. Authorization is
 * delegated to OrderPolicy@update.
 */
final class UpdateOrderDetailsRequest extends FormRequest
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
            'customer_name'     => ['required', 'string', 'max:120'],
            'customer_contact'  => ['required', 'string', 'max:15'],
            'order_category_id' => ['required', 'integer', Rule::exists('order_categories', 'id')->where('is_active', true)],
            'order_type_id'     => ['required', 'integer', Rule::exists('order_types', 'id')->where('is_active', true)],
            'order_date'        => ['required', 'date_format:Y-m-d'],
        ];
    }
}
