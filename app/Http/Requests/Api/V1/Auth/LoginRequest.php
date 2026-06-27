<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, string[]> */
    public function rules(): array
    {
        return [
            'mobile_number' => ['required', 'string', 'digits:10'],
            'password'      => ['required', 'string', 'min:6'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'mobile_number.required' => 'Mobile number is required.',
            'mobile_number.string'   => 'Mobile number must be a string.',
            'mobile_number.digits'   => 'Mobile number must be exactly 10 digits.',
            'password.required'      => 'Password is required.',
            'password.string'        => 'Password must be a string.',
            'password.min'           => 'Password must be at least 6 characters.',
        ];
    }
}
