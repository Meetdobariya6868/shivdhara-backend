<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the authenticated user's own profile update (name + mobile).
 * Any authenticated role may edit their own profile — the endpoint always acts
 * on $request->user(), never an arbitrary id, so no ownership check is needed.
 */
final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name'          => ['required', 'string', 'max:120'],
            'mobile_number' => [
                'required', 'string', 'digits:10',
                Rule::unique('users', 'mobile_number')->ignore($userId),
            ],
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
