<?php

declare(strict_types=1);

namespace App\Application\Services\Catalog;

use App\Application\Services\BaseService;
use App\Domain\Contracts\DesignRepositoryInterface;
use App\Domain\Contracts\DesignVariantRepositoryInterface;
use App\Models\Design;
use App\Models\DesignVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Business logic for the catalogue-management module (admin "Show products"):
 * browsing designs and editing a variant's purchase / sell rate. All other
 * catalogue attributes are immutable through this module.
 */
final class CatalogService extends BaseService
{
    public function __construct(
        private readonly DesignRepositoryInterface $designRepository,
        private readonly DesignVariantRepositoryInterface $variantRepository,
    ) {}

    /**
     * A filtered, paginated page of designs for the catalogue list.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Design>
     */
    public function paginateDesigns(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->designRepository->paginate($filters, $perPage);
    }

    /**
     * A single design with its variants for the detail screen.
     *
     * @throws ModelNotFoundException when the design does not exist.
     */
    public function getDesign(int $id): Design
    {
        return $this->designRepository->findWithVariants($id)
            ?? throw (new ModelNotFoundException())->setModel(Design::class, [$id]);
    }

    /**
     * Every design with its company and variants, for the full catalogue export.
     *
     * @return Collection<int, Design>
     */
    public function designsForExport(): Collection
    {
        return $this->designRepository->allWithVariants();
    }

    /**
     * Update only a variant's purchase / sell rate. Wrapped in a transaction for
     * consistency with the rest of the write layer.
     *
     * @param  array{purchase_rate: float|int|string, sell_rate: float|int|string}  $rates
     */
    public function updateVariantRates(DesignVariant $variant, array $rates): DesignVariant
    {
        return DB::transaction(
            fn (): DesignVariant => $this->variantRepository->updateRates($variant, $rates),
        );
    }
}
