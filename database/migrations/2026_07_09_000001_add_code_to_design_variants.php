<?php

declare(strict_types=1);

use App\Domain\Support\CatalogCodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gives every design variant its own unique code (company + design +
 * size/finish/thickness), and repairs design codes that currently collide
 * across different companies.
 *
 * Raw DB queries are used for the backfill so soft-delete scopes don't hide
 * rows that still occupy the unique index, and so no model events fire.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('design_variants', 'code')) {
            Schema::table('design_variants', function (Blueprint $table): void {
                $table->string('code', 60)->nullable()->after('thickness')->index();
            });
        }

        $this->backfillVariantCodes();

        // Enforce global uniqueness now that every row has a code.
        Schema::table('design_variants', function (Blueprint $table): void {
            $table->unique('code', 'design_variants_code_unique');
        });

        $this->dedupeDesignCodesAcrossCompanies();
    }

    public function down(): void
    {
        Schema::table('design_variants', function (Blueprint $table): void {
            $table->dropUnique('design_variants_code_unique');
            $table->dropIndex(['code']);
            $table->dropColumn('code');
        });
        // Design-code repairs are historical data fixes and are not reversed.
    }

    /** Assign a fresh, globally-unique code to every variant missing one. */
    private function backfillVariantCodes(): void
    {
        $rows = DB::table('design_variants as dv')
            ->join('designs as d', 'd.id', '=', 'dv.design_id')
            ->select('dv.id', 'dv.size', 'dv.finish', 'dv.thickness', 'd.company_id', 'd.design_name')
            ->whereNull('dv.code')
            ->get();

        foreach ($rows as $row) {
            $seed = CatalogCodeGenerator::variantSeed(
                (int) $row->company_id,
                (string) $row->design_name,
                (string) $row->size,
                (string) $row->finish,
                (string) $row->thickness,
            );

            $code = CatalogCodeGenerator::unique(
                $seed,
                static fn (string $c): bool => DB::table('design_variants')->where('code', $c)->exists(),
            );

            DB::table('design_variants')->where('id', $row->id)->update(['code' => $code]);
        }
    }

    /**
     * Repair only the design codes that are currently shared across more than
     * one company. The first design keeps its code; the rest are regenerated
     * with a company-aware code so the cross-company clash is resolved with the
     * minimum change.
     */
    private function dedupeDesignCodesAcrossCompanies(): void
    {
        $conflicts = DB::table('designs')
            ->select('design_code')
            ->whereNull('deleted_at')
            ->groupBy('design_code')
            ->havingRaw('COUNT(DISTINCT company_id) > 1')
            ->pluck('design_code');

        foreach ($conflicts as $code) {
            $designs = DB::table('designs')
                ->where('design_code', $code)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            foreach ($designs->skip(1) as $design) {
                $new = CatalogCodeGenerator::unique(
                    CatalogCodeGenerator::designSeed((int) $design->company_id, (string) $design->design_name),
                    static fn (string $c): bool => DB::table('designs')
                        ->where('company_id', $design->company_id)
                        ->where('design_code', $c)
                        ->exists(),
                );

                DB::table('designs')->where('id', $design->id)->update(['design_code' => $new]);
            }
        }
    }
};
