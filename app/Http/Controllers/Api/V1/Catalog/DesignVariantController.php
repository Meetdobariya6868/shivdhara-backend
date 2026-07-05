<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Application\Services\Catalog\CatalogService;
use App\Domain\Contracts\DesignVariantRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Catalog\UpdateDesignVariantRequest;
use App\Http\Resources\Api\V1\Catalog\DesignVariantResource;
use App\Http\Resources\Api\V1\Catalog\VariantRateResource;
use App\Models\DesignVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Catalogue endpoints: the order-flow autocomplete search (all roles) and the
 * admin rate update.
 */
final class DesignVariantController extends Controller
{
    public function __construct(
        private readonly DesignVariantRepositoryInterface $repository,
        private readonly CatalogService $catalogService,
    ) {}

    /**
     * GET /design-variants/search?q={query}
     *
     * Returns up to 30 active variants whose design name, design code, or
     * company name contains the query (min 2 chars). Powers the Add-Item
     * autocomplete; selecting a row links the order item to that exact variant.
     */
    public function search(Request $request): JsonResponse
    {
        $query = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        if (mb_strlen($query) < 2) {
            return $this->success(data: [], message: 'Results retrieved.');
        }

        return $this->success(
            data: DesignVariantResource::collection($this->repository->search($query)),
            message: 'Results retrieved.',
        );
    }

    /**
     * PATCH /design-variants/{designVariant}
     *
     * Update only a variant's purchase / sell rate (admin only, enforced in
     * UpdateDesignVariantRequest). All other catalogue attributes are immutable.
     */
    public function update(UpdateDesignVariantRequest $request, DesignVariant $designVariant): JsonResponse
    {
        $updated = $this->catalogService->updateVariantRates($designVariant, [
            'purchase_rate' => $request->validated('purchase_rate'),
            'sell_rate'     => $request->validated('sell_rate'),
        ]);

        return $this->success(
            data: VariantRateResource::make($updated),
            message: 'Rates updated successfully.',
        );
    }
}
