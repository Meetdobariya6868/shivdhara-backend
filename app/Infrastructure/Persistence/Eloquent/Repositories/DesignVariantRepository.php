<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Contracts\DesignVariantRepositoryInterface;
use App\Models\DesignVariant;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DesignVariantRepository implements DesignVariantRepositoryInterface
{
    /** InnoDB's default innodb_ft_min_token_size — shorter tokens are ignored by FULLTEXT. */
    private const FT_MIN_TOKEN = 3;

    /**
     * Search the active catalogue by design name, design code, or company name.
     *
     * Runs two FULLTEXT-driven queries unioned together — one driven by the
     * designs index, one by the companies index. A single OR'd query cannot use
     * either FULLTEXT index (it full-scans, ~940ms at 100k); this stays ~1-2ms
     * at the same size because each side hits its own index as the access path.
     *
     * Tokens shorter than the FULLTEXT minimum fall back to an index-friendly
     * prefix LIKE so 2-character queries still return results.
     *
     * @return Collection<int, DesignVariant>
     */
    public function search(string $query, int $limit = 30): Collection
    {
        $query = trim($query);
        if ($query === '') {
            return new Collection();
        }

        $tokens = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $ftTokens = array_values(array_filter(
            $tokens,
            static fn (string $t): bool => mb_strlen($t) >= self::FT_MIN_TOKEN,
        ));

        if ($ftTokens !== []) {
            // Every long token required as a prefix: "+onyx* +grey*".
            $boolean = implode(' ', array_map(
                static fn (string $t): string => '+'.self::sanitizeToken($t).'*',
                $ftTokens,
            ));

            $designSide = $this->sideQuery('designs', $limit, static fn (Builder $q) => $q->whereRaw(
                'MATCH(designs.design_name) AGAINST (? IN BOOLEAN MODE)',
                [$boolean],
            ));
            $companySide = $this->sideQuery('companies', $limit, static fn (Builder $q) => $q->whereRaw(
                'MATCH(companies.company_name) AGAINST (? IN BOOLEAN MODE)',
                [$boolean],
            ));
        } else {
            // Too short for FULLTEXT — leading-anchored LIKE still uses the B-tree index.
            $prefix = addcslashes($query, '%_\\').'%';

            $designSide = $this->sideQuery('designs', $limit, static fn (Builder $q) => $q->where(
                'designs.design_name',
                'like',
                $prefix,
            ));
            $companySide = $this->sideQuery('companies', $limit, static fn (Builder $q) => $q->where(
                'companies.company_name',
                'like',
                $prefix,
            ));
        }

        // Variant codes are random substrings (like design codes), so match them
        // with a substring LIKE on design_variants.code — regardless of token
        // length, so a user can paste a full or partial variant code.
        $variantCodeSide = $this->sideQuery('designs', $limit, static fn (Builder $q): Builder => $q->where(
            'design_variants.code',
            'like',
            '%'.addcslashes($query, '%_\\').'%',
        ));

        /** @var Collection<int, DesignVariant> $variants */
        $variants = $designSide->union($companySide)->union($variantCodeSide)->get();

        return $variants
            ->load('design.company')
            ->unique('id')
            ->sortBy(fn (DesignVariant $v): string => mb_strtolower(
                ($v->design?->company?->company_name ?? '').' '.($v->design?->design_name ?? ''),
            ))
            ->take($limit)
            ->values();
    }

    /**
     * One side of the union, driven by the table holding the FULLTEXT index
     * ($driver = 'designs' or 'companies') so MySQL uses it as the access path,
     * while still selecting/hydrating design_variants. The $match closure adds
     * that side's FULLTEXT (or LIKE) predicate.
     */
    private function sideQuery(string $driver, int $limit, Closure $match): Builder
    {
        $query = DesignVariant::query()->from($driver)->select('design_variants.*');

        if ($driver === 'designs') {
            $query->join('design_variants', 'design_variants.design_id', '=', 'designs.id')
                ->join('companies', 'designs.company_id', '=', 'companies.id');
        } else { // companies
            $query->join('designs', 'designs.company_id', '=', 'companies.id')
                ->join('design_variants', 'design_variants.design_id', '=', 'designs.id');
        }

        $query->where('design_variants.is_active', true)
            ->whereNull('design_variants.deleted_at')
            ->where('designs.is_active', true)
            ->whereNull('designs.deleted_at')
            ->where('companies.is_active', true)
            ->whereNull('companies.deleted_at')
            ->limit($limit);

        $match($query);

        return $query;
    }

    /** Strip boolean-mode operators so user input can't alter the query semantics. */
    private static function sanitizeToken(string $token): string
    {
        return str_replace(['+', '-', '*', '"', '(', ')', '~', '<', '>', '@'], '', $token);
    }

    /**
     * @param  array{purchase_rate: float|int|string, sell_rate: float|int|string}  $rates
     */
    public function updateRates(DesignVariant $variant, array $rates): DesignVariant
    {
        $variant->update([
            'purchase_rate' => $rates['purchase_rate'],
            'sell_rate'     => $rates['sell_rate'],
        ]);

        return $variant->refresh();
    }
}
