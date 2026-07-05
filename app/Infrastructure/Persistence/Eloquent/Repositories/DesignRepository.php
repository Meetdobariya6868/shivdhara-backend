<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\DesignRepositoryInterface;
use App\Models\Design;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DesignRepository implements DesignRepositoryInterface
{
    /** InnoDB's default innodb_ft_min_token_size — shorter tokens are ignored by FULLTEXT. */
    private const FT_MIN_TOKEN = 3;

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Design>
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Design::query()
            ->with('company:id,company_name')
            ->withCount('variants');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $this->applySearch($query, $search);
        }

        /** @var LengthAwarePaginator<int, Design> $result */
        $result = $query->orderBy('design_name')->paginate($perPage);

        return $result;
    }

    public function findWithVariants(int $id): ?Design
    {
        /** @var Design|null $design */
        $design = Design::query()
            ->with([
                'company:id,company_name',
                'variants' => static fn ($q) => $q
                    ->orderBy('size')
                    ->orderBy('finish')
                    ->orderBy('thickness'),
            ])
            ->find($id);

        return $design;
    }

    /**
     * Narrow the query to designs matching the search across three fields —
     * design name, design code, and company name.
     *
     * Each field is matched by its own FULLTEXT index (a single OR'd MATCH can
     * use neither index and full-scans). We union the design-driven and
     * company-driven id sets and constrain the outer query by that set, so both
     * indexes are used as access paths and the result still paginates/eager-loads
     * normally. Tokens shorter than the FULLTEXT minimum fall back to prefix LIKE.
     *
     * @param  Builder<Design>  $query
     */
    private function applySearch(Builder $query, string $search): void
    {
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $ftTokens = array_values(array_filter(
            $tokens,
            static fn (string $t): bool => mb_strlen($t) >= self::FT_MIN_TOKEN,
        ));

        // Design name via the (design_name, design_code) FULLTEXT index.
        $nameIds = Design::query()->select('designs.id');
        // Company name via the companies FULLTEXT index.
        $companyIds = Design::query()
            ->select('designs.id')
            ->join('companies', 'companies.id', '=', 'designs.company_id')
            ->whereNull('companies.deleted_at');

        if ($ftTokens !== []) {
            // Every long token required as a prefix: "+onyx* +grey*".
            $boolean = implode(' ', array_map(
                static fn (string $t): string => '+'.self::sanitizeToken($t).'*',
                $ftTokens,
            ));

            $nameIds->whereRaw(
                'MATCH(designs.design_name, designs.design_code) AGAINST (? IN BOOLEAN MODE)',
                [$boolean],
            );
            $companyIds->whereRaw(
                'MATCH(companies.company_name) AGAINST (? IN BOOLEAN MODE)',
                [$boolean],
            );
        } else {
            // Too short for FULLTEXT — leading-anchored LIKE.
            $prefix = addcslashes($search, '%_\\').'%';
            $nameIds->where('designs.design_name', 'like', $prefix);
            $companyIds->where('companies.company_name', 'like', $prefix);
        }

        // Design codes are random identifiers (e.g. "d642326b"), so match them
        // with a substring LIKE — reliable for a full or partial code, which
        // FULLTEXT can't do for arbitrary substrings.
        $codeIds = Design::query()
            ->select('designs.id')
            ->where('designs.design_code', 'like', '%'.addcslashes($search, '%_\\').'%');

        $query->whereIn('designs.id', $nameIds->union($codeIds)->union($companyIds));
    }

    /** Strip boolean-mode operators so user input can't alter the query semantics. */
    private static function sanitizeToken(string $token): string
    {
        return str_replace(['+', '-', '*', '"', '(', ')', '~', '<', '>', '@'], '', $token);
    }
}
