<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Domain\Contracts\DesignVariantRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Catalog\DesignVariantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Catalogue read endpoints for the order create / edit flow.
 */
final class DesignVariantController extends Controller
{
    public function __construct(
        private readonly DesignVariantRepositoryInterface $repository,
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
}
