<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates an order quotation PDF request. Authorized to whoever can view the
 * order (admin, or the salesman who created it) via OrderPolicy@view.
 */
final class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order && ($this->user()?->can('view', $order) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'format' => ['required', Rule::in(['name', 'code'])],
        ];
    }
}
