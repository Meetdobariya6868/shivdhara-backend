<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\ProductCatalogRepositoryInterface;
use App\Models\Company;
use App\Models\Design;
use App\Models\DesignVariant;
use Illuminate\Support\Str;

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
            ['design_code' => $this->makeDesignCode($designName), 'is_active' => true],
        );

        return DesignVariant::query()->firstOrCreate(
            [
                'design_id' => $design->id,
                'size'      => trim($size),
                'finish'    => trim($finish),
                'thickness' => trim($thickness),
            ],
            [
                'purchase_rate' => $purchaseRate,
                'sell_rate'     => $sellRate,
                'is_active'     => true,
            ],
        );
    }

    /**
     * Build a design code from an 8-character hash of the design name.
     * Deterministic (the same name always yields the same code) and short
     * enough to type/search, at a negligible collision risk per company.
     */
    private function makeDesignCode(string $designName): string
    {
        return Str::substr(md5($designName), 0, 8);
    }
}
