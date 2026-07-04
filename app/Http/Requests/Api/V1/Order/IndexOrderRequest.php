<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the query parameters for the paginated admin order list.
 * Admin-only (OrderPolicy@viewAny). Filter ids are validated as integers only
 * — not `exists` — so filtering by a since-deactivated category/type or a
 * soft-deleted salesman still works (it simply narrows the result set).
 */
final class IndexOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Order::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'page'              => ['sometimes', 'integer', 'min:1'],
            'per_page'          => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search'            => ['sometimes', 'nullable', 'string', 'max:120'],
            'date_from'         => ['sometimes', 'nullable', 'date'],
            'date_to'           => ['sometimes', 'nullable', 'date', 'after_or_equal:date_from'],
            'order_category_id' => ['sometimes', 'nullable', 'integer'],
            'order_type_id'     => ['sometimes', 'nullable', 'integer'],
            'creator_id'        => ['sometimes', 'nullable', 'integer'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'The "to" date must be on or after the "from" date.',
        ];
    }
}
