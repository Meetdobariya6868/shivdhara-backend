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
     * Fetch an existing active variant by id, or null if missing/inactive.
     * Used when the client selected a row from the autocomplete and sent its
     * design_variant_id — links the order item to that exact variant with no
     * risk of creating a near-duplicate.
     */
    public function findVariant(int $id): ?DesignVariant;

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

    /**
     * Overwrite a variant's catalogue purchase / sell rate with the given values,
     * persisting only when either actually changed. Used so the rates typed on an
     * order update the selected catalogue variant. Returns the (possibly updated)
     * variant.
     */
    public function syncVariantRates(DesignVariant $variant, float $purchaseRate, float $sellRate): DesignVariant;
}
