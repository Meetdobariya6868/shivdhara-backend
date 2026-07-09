<?php

declare(strict_types=1);

namespace App\Domain\Support;

/**
 * Generates the catalogue's single product code.
 *
 * A code lives on a design variant and is an 8-character hash of a "seed" that
 * captures the variant's full identity — company, design name, size, finish,
 * thickness and rates — so no two variants ever share a code, even across
 * companies. {@see self::unique()} re-salts and re-hashes until the caller's
 * existence check passes, so even a rare hash collision resolves to distinct
 * codes.
 *
 * Pure and framework-free (the persistence-aware "does this code already exist"
 * check is injected), so it is trivially testable and reusable across the order
 * flow, the catalogue seeder, and data migrations.
 */
final class CatalogCodeGenerator
{
    private const LENGTH = 8;

    /**
     * @deprecated Retained only for the historical migration that predates the
     * single per-variant code. Current code generation uses {@see self::variantSeed()}.
     */
    public static function designSeed(int $companyId, string $designName): string
    {
        return $companyId.'|'.trim($designName);
    }

    /**
     * Seed for a variant's code — the full factor set that identifies exactly
     * one variant: company + design + size/finish/thickness + purchase/sell rate.
     *
     * Rates are optional so the historical backfill migration (which predates
     * including rates) keeps producing the same seeds it always did.
     */
    public static function variantSeed(
        int $companyId,
        string $designName,
        string $size,
        string $finish,
        string $thickness,
        ?float $purchaseRate = null,
        ?float $sellRate = null,
    ): string {
        $parts = [
            $companyId,
            trim($designName),
            trim($size),
            trim($finish),
            trim($thickness),
        ];

        if ($purchaseRate !== null) {
            $parts[] = number_format($purchaseRate, 2, '.', '');
        }
        if ($sellRate !== null) {
            $parts[] = number_format($sellRate, 2, '.', '');
        }

        return implode('|', $parts);
    }

    /**
     * Derive a unique code from the seed. The first candidate is a plain hash of
     * the seed; if $exists reports it as taken, an incrementing salt is folded in
     * and the hash retried until a free code is found (deterministic per seed
     * given the same existing set).
     *
     * @param  callable(string): bool  $exists  Returns true if the code is already taken.
     */
    public static function unique(string $seed, callable $exists): string
    {
        $salt = 0;

        do {
            $raw = $salt === 0 ? $seed : $seed.'#'.$salt;
            $code = substr(md5($raw), 0, self::LENGTH);
            $salt++;
        } while ($exists($code));

        return $code;
    }
}
