<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Catalog;

use App\Models\DesignVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One selectable catalogue variant for the Add-Item autocomplete.
 * Carries the variant id (so the order can link to it directly, guaranteeing
 * exact reuse) plus the display fields and the nested design + company.
 *
 * @mixin DesignVariant
 */
final class DesignVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'size'          => $this->size,
            'finish'        => $this->finish,
            'thickness'     => $this->thickness,
            'purchase_rate' => $this->purchase_rate,
            'sell_rate'     => $this->sell_rate,
            'design'        => [
                'id'          => $this->design?->id,
                'design_name' => $this->design?->design_name,
                'company'     => [
                    'id'           => $this->design?->company?->id,
                    'company_name' => $this->design?->company?->company_name,
                ],
            ],
        ];
    }
}
