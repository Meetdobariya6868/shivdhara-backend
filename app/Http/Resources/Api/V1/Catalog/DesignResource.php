<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Catalog;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A design row for the catalogue list: code, name, its company, and how many
 * variants it has. Company is expected to be eager-loaded and the variant count
 * present via withCount('variants').
 *
 * @mixin Design
 */
final class DesignResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'design_code'    => $this->design_code,
            'design_name'    => $this->design_name,
            'is_active'      => $this->is_active,
            'variants_count' => $this->whenCounted('variants'),
            'company'        => [
                'id'           => $this->company?->id,
                'company_name' => $this->company?->company_name,
            ],
        ];
    }
}
