<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Serialises a generated quotation share link.
 *
 * Wraps the {url, expires_at} array returned by QuotationService@shareUrl so the
 * share-link endpoint keeps the same API-Resource contract as the rest of the
 * module.
 *
 * @property array{url: string, expires_at: string} $resource
 */
final class QuotationShareLinkResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'url' => $this->resource['url'],
            'expires_at' => $this->resource['expires_at'],
        ];
    }
}
