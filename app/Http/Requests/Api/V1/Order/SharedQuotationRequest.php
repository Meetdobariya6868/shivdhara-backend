<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Application\Services\Order\QuotationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates public, signed access to a quotation PDF (the WhatsApp share link).
 *
 * Authorization is handled entirely by the `signed` route middleware: the
 * tamper-proof, time-limited signature both authenticates the request and
 * guarantees the `format` was not altered. No authenticated user is involved,
 * so authorize() simply returns true.
 */
final class SharedQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'format' => ['required', Rule::in(QuotationService::MODES)],
        ];
    }
}
