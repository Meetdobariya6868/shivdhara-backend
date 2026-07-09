<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\ProductCatalogRepositoryInterface;
use App\Domain\Support\CatalogCodeGenerator;
use App\Models\Company;
use App\Models\Design;
use App\Models\DesignVariant;

/**
 * Resolves the catalogue graph for an order item.
 *
 * Each level is matched on its natural identity and created only when absent:
 *   Company   → company_name
 *   Design    → (company_id, design_name)
 *   Variant   → (design_id, size, finish, thickness)
 *
 * Existing records are reused as-is; only newly created variants take the
 * supplied rates as their catalogue defaults (an order item always stores its
 * own rates, so reused variants are never mutated here).
 */
class ProductCatalogRepository implements ProductCatalogRepositoryInterface
{
    public function findVariant(int $id): ?DesignVariant
    {
        return DesignVariant::query()
            ->where('is_active', true)
            ->find($id);
    }

    public function resolveVariant(
        string $companyName,
        string $designName,
        string $size,
        string $finish,
        string $thickness,
        float $purchaseRate,
        float $sellRate,
    ): DesignVariant {
        $company = Company::query()->firstOrCreate(
            ['company_name' => trim($companyName)],
            ['is_active' => true],
        );

        $designName = trim($designName);

        $design = Design::query()->firstOrCreate(
            ['company_id' => $company->id, 'design_name' => $designName],
            ['is_active' => true],
        );

        $size      = trim($size);
        $finish    = trim($finish);
        $thickness = trim($thickness);

        return DesignVariant::query()->firstOrCreate(
            [
                'design_id' => $design->id,
                'size'      => $size,
                'finish'    => $finish,
                'thickness' => $thickness,
            ],
            [
                'code'          => $this->makeVariantCode(
                    $company->id,
                    $designName,
                    $size,
                    $finish,
                    $thickness,
                    $purchaseRate,
                    $sellRate,
                ),
                'purchase_rate' => $purchaseRate,
                'sell_rate'     => $sellRate,
                'is_active'     => true,
            ],
        );
    }

    public function syncVariantRates(DesignVariant $variant, float $purchaseRate, float $sellRate): DesignVariant
    {
        // Compare at 2-decimal precision (the stored scale) to avoid float noise
        // and needless writes when nothing changed.
        $changed = number_format((float) $variant->purchase_rate, 2, '.', '') !== number_format($purchaseRate, 2, '.', '')
            || number_format((float) $variant->sell_rate, 2, '.', '') !== number_format($sellRate, 2, '.', '');

        if ($changed) {
            $variant->purchase_rate = $purchaseRate;
            $variant->sell_rate = $sellRate;
            $variant->save();
        }

        return $variant;
    }

    /**
     * The catalogue's single product code, unique across all variants. Derived
     * from the variant's full identity — company + design + size/finish/thickness
     * + rates — so two variants never share a code, even across companies.
     */
    private function makeVariantCode(
        int $companyId,
        string $designName,
        string $size,
        string $finish,
        string $thickness,
        float $purchaseRate,
        float $sellRate,
    ): string {
        return CatalogCodeGenerator::unique(
            CatalogCodeGenerator::variantSeed(
                $companyId,
                $designName,
                $size,
                $finish,
                $thickness,
                $purchaseRate,
                $sellRate,
            ),
            static fn (string $code): bool => DesignVariant::withTrashed()
                ->where('code', $code)
                ->exists(),
        );
    }
}
