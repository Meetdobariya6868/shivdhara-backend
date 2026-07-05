<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\DesignVariant;
use Illuminate\Database\Eloquent\Collection;

interface DesignVariantRepositoryInterface
{
    /**
     * Search active design variants by design name, design code, or company
     * name. Each result includes its nested design + company for display.
     *
     * @return Collection<int, DesignVariant>
     */
    public function search(string $query, int $limit = 30): Collection;

    /**
     * Update only the rate columns (purchase_rate, sell_rate) of a variant and
     * return the refreshed model. No other attributes are touched.
     *
     * @param  array{purchase_rate: float|int|string, sell_rate: float|int|string}  $rates
     */
    public function updateRates(DesignVariant $variant, array $rates): DesignVariant;
}
