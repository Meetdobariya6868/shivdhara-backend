<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Models\Design;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface DesignRepositoryInterface
{
    /**
     * Every design with its company and variants, ordered for a full catalogue
     * export. Relations are eager-loaded to avoid N+1 while writing rows.
     *
     * @return Collection<int, Design>
     */
    public function allWithVariants(): Collection;

    /**
     * A page of designs (with their company and variant count) matching the
     * optional filters: `search` (design name/code) and `company_id`.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Design>
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    /**
     * A single design with its company and all its variants (ordered), or null.
     */
    public function findWithVariants(int $id): ?Design;
}
