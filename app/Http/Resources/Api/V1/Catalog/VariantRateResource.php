<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Catalog;

use App\Models\DesignVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A design variant for the catalogue-management view: the immutable description
 * (size / finish / thickness) plus the editable purchase / sell rates. Used both
 * inside DesignDetailResource and as the rate-update response.
 *
 * @mixin DesignVariant
 */
final class VariantRateResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'size'          => $this->size,
            'finish'        => $this->finish,
            'thickness'     => $this->thickness,
            'purchase_rate' => $this->purchase_rate,
            'sell_rate'     => $this->sell_rate,
            'is_active'     => $this->is_active,
        ];
    }
}
