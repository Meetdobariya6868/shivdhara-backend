<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a single product-image upload for an order item.
 * Only users authorised to create orders may upload item photos.
 */
final class UploadOrderItemImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image.max'   => 'The image may not be larger than 4 MB.',
            'image.mimes' => 'The image must be a JPG, PNG or WEBP file.',
        ];
    }
}
