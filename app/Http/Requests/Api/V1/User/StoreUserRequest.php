<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creation of a new salesman account.
 * Authorization is delegated to UserPolicy@create via the controller.
 */
final class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:120'],
            'mobile_number'     => ['required', 'string', 'digits:10', Rule::unique('users', 'mobile_number')],
            'password'          => ['required', 'string', 'min:6', 'max:255'],
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
