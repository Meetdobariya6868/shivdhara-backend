<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a design variant's rate update. Only purchase_rate and sell_rate
 * are accepted — every other catalogue attribute is immutable here. Admin-only.
 *
 * Bounds mirror the design_variants decimal(12,2) columns.
 */
final class UpdateDesignVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'purchase_rate' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'sell_rate'     => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'purchase_rate.required' => 'Purchase rate is required.',
            'sell_rate.required'     => 'Sell rate is required.',
        ];
    }
}
