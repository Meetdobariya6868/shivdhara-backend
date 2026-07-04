<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a salesman permission change (currently the "can create orders"
 * flag). Admin-only, authorized via UserPolicy@update.
 */
final class UpdatePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', User::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'can_create_orders' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'can_create_orders.required' => 'The create-orders permission is required.',
        ];
    }
}
