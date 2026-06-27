<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Domain\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates a block / unblock status change.
 * Self-status-change is blocked by UserPolicy@changeStatus in the controller.
 */
final class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Fine-grained authorization handled in the controller via policy.
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(UserStatus::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
        ];
    }
}
