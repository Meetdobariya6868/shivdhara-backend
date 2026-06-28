<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\DesignVariant;

/**
 * Persistence contract for the product catalogue (companies → designs → variants).
 *
 * When an order item is created from free-typed product text, the catalogue grows
 * organically: the matching company/design/variant is found or created so the same
 * product is reused across future orders.
 */
interface ProductCatalogRepositoryInterface
{
    /**
     * Resolve (find or create) the design variant identified by the given free-text
     * product attributes. New variants are seeded with the supplied default rates.
     */
    public function resolveVariant(
        string $companyName,
        string $designName,
        string $size,
        string $finish,
        string $thickness,
        float $purchaseRate,
        float $sellRate,
    ): DesignVariant;
}
