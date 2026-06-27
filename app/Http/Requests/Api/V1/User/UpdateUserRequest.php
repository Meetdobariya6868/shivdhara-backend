<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates updates to an existing salesman's profile.
 * The route-model-bound {user} id is excluded from the unique check.
 */
final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', User::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name'              => ['required', 'string', 'max:120'],
            'mobile_number'     => [
                'required', 'string', 'digits:10',
                Rule::unique('users', 'mobile_number')->ignore($userId),
            ],
            'can_create_orders' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'mobile_number.unique' => 'This mobile number is already registered.',
            'mobile_number.digits' => 'Mobile number must be exactly 10 digits.',
        ];
    }
}
