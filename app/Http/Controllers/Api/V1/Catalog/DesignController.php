<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Application\Services\Catalog\CatalogService;
use App\Application\Services\Catalog\DesignExportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Catalog\IndexDesignRequest;
use App\Http\Resources\Api\V1\Catalog\DesignDetailResource;
use App\Http\Resources\Api\V1\Catalog\DesignResource;
use App\Models\Design;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Catalogue-management endpoints (admin "Show products"): browse designs and
 * open a design's variants. Thin orchestration only — logic lives in CatalogService.
 */
final class DesignController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService,
        private readonly DesignExportService $designExportService,
    ) {}

    /**
     * GET /designs/export — download every design + its variants as an .xlsx
     * (admin only). Registered before the /designs/{design} route so "export"
     * is never treated as a model id.
     */
    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin() ?? false, Response::HTTP_FORBIDDEN);

        $contents = $this->designExportService->toXlsx($this->catalogService->designsForExport());

        return response()->streamDownload(
            static fn () => print($contents),
            'designs.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        );
    }

    /**
     * GET /designs — paginated, server-filtered design list (admin only).
     * Search (design name/code) and company filter run in SQL against indexes;
     * authorization is enforced in IndexDesignRequest.
     */
    public function index(IndexDesignRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 20);

        $paginator = $this->catalogService->paginateDesigns($filters, $perPage);

        return $this->paginated(
            $paginator,
            DesignResource::collection($paginator->getCollection()),
            'Designs retrieved.',
        );
    }

    /**
     * GET /designs/{design} — a design with all its variants (admin only).
     */
    public function show(Request $request, Design $design): JsonResponse
    {
        abort_unless($request->user()?->isAdmin() ?? false, Response::HTTP_FORBIDDEN);

        return $this->success(
            data: DesignDetailResource::make($this->catalogService->getDesign((int) $design->id)),
            message: 'Design retrieved.',
        );
    }
}
