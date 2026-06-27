<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an admin-initiated password reset for a salesman.
 */
final class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', User::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ];
    }
}
