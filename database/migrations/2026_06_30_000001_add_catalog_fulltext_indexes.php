<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FULLTEXT indexes powering the Add-Item catalogue autocomplete.
 *
 * A leading-wildcard `LIKE '%term%'` cannot use a B-tree index and full-scans,
 * which does not hold up at 100k+ variants. InnoDB FULLTEXT + MATCH…AGAINST
 * keeps catalogue search in single-digit milliseconds at that scale.
 *
 * The designs index spans (design_name, design_code) because a MATCH must list
 * exactly the columns of one FULLTEXT index.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `designs` ADD FULLTEXT `designs_search_ft` (`design_name`, `design_code`)');
        DB::statement('ALTER TABLE `companies` ADD FULLTEXT `companies_name_ft` (`company_name`)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `designs` DROP INDEX `designs_search_ft`');
        DB::statement('ALTER TABLE `companies` DROP INDEX `companies_name_ft`');
    }
};
