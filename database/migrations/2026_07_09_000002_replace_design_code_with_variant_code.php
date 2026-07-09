<?php

declare(strict_types=1);

use App\Domain\Support\CatalogCodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Collapses the catalogue to a single product code.
 *
 * The design-level `designs.design_code` is removed; the one surviving code is
 * `design_variants.code`, generated from the variant's full identity (company +
 * design + size/finish/thickness + rates). A design is now uniquely identified
 * by (company_id, design_name), and the catalogue FULLTEXT index drops the
 * design_code column it used to span.
 */
return new class extends Migration
{
    public function up(): void
    {
        // The FULLTEXT and unique indexes both span design_code — drop them
        // before the column can go.
        DB::statement('ALTER TABLE `designs` DROP INDEX `designs_search_ft`');
        Schema::table('designs', function (Blueprint $table): void {
            $table->dropUnique('designs_company_id_design_code_unique');
        });

        // A design's natural identity is now (company, name).
        Schema::table('designs', function (Blueprint $table): void {
            $table->unique(['company_id', 'design_name'], 'designs_company_id_design_name_unique');
        });

        Schema::table('designs', function (Blueprint $table): void {
            $table->dropColumn('design_code');
        });

        // Recreate the catalogue search index on the name alone.
        DB::statement('ALTER TABLE `designs` ADD FULLTEXT `designs_search_ft` (`design_name`)');

        // Regenerate every variant code from the full factor set so all codes
        // follow one rule. Drop the unique index first so the codes can be
        // cleared and refilled without transient collisions.
        Schema::table('design_variants', function (Blueprint $table): void {
            $table->dropUnique('design_variants_code_unique');
        });

        $this->regenerateVariantCodes();

        Schema::table('design_variants', function (Blueprint $table): void {
            $table->unique('code', 'design_variants_code_unique');
        });
    }

    public function down(): void
    {
        // Re-add the design-level code (nullable — original values are unknown).
        DB::statement('ALTER TABLE `designs` DROP INDEX `designs_search_ft`');
        Schema::table('designs', function (Blueprint $table): void {
            $table->dropUnique('designs_company_id_design_name_unique');
            $table->string('design_code', 60)->nullable()->after('company_id');
        });
        DB::statement('ALTER TABLE `designs` ADD FULLTEXT `designs_search_ft` (`design_name`, `design_code`)');
    }

    /** Assign every variant a fresh, globally-unique code from its full identity. */
    private function regenerateVariantCodes(): void
    {
        $rows = DB::table('design_variants as dv')
            ->join('designs as d', 'd.id', '=', 'dv.design_id')
            ->select(
                'dv.id',
                'dv.size',
                'dv.finish',
                'dv.thickness',
                'dv.purchase_rate',
                'dv.sell_rate',
                'd.company_id',
                'd.design_name',
            )
            ->get();

        // Clear first so a new code can never clash with an old, not-yet-updated one.
        DB::table('design_variants')->update(['code' => null]);

        foreach ($rows as $row) {
            $seed = CatalogCodeGenerator::variantSeed(
                (int) $row->company_id,
                (string) $row->design_name,
                (string) $row->size,
                (string) $row->finish,
                (string) $row->thickness,
                (float) $row->purchase_rate,
                (float) $row->sell_rate,
            );

            $code = CatalogCodeGenerator::unique(
                $seed,
                static fn (string $c): bool => DB::table('design_variants')->where('code', $c)->exists(),
            );

            DB::table('design_variants')->where('id', $row->id)->update(['code' => $code]);
        }
    }
};
