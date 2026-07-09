<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Catalog;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A design with its company and all its variants, for the detail screen where
 * an admin edits each variant's rates. Company and variants are eager-loaded.
 *
 * @mixin Design
 */
final class DesignDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'design_name' => $this->design_name,
            'is_active'   => $this->is_active,
            'company'     => [
                'id'           => $this->company?->id,
                'company_name' => $this->company?->company_name,
            ],
            'variants'    => VariantRateResource::collection($this->whenLoaded('variants')),
        ];
    }
}
