<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Catalog;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A design row for the catalogue list: name, its company, how many variants it
 * has, and — when the design has exactly one variant — that variant's code (a
 * design with several variants has no single code). Company is eager-loaded,
 * the variant count comes from withCount('variants'), and `sole_variant_code`
 * is a select subquery added by the repository.
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
            'design_name'    => $this->design_name,
            'is_active'      => $this->is_active,
            'variants_count' => $this->whenCounted('variants'),
            // Shown on the list card only when unambiguous (single-variant design).
            'code'           => (int) $this->variants_count === 1 ? $this->sole_variant_code : null,
            'company'        => [
                'id'           => $this->company?->id,
                'company_name' => $this->company?->company_name,
            ],
        ];
    }
}
