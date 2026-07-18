<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two indexes that make the catalogue browse list (19k+ designs) fast.
 *
 * 1. (created_at, id) — the list orders designs newest-first (created_at DESC,
 *    id DESC). Without this it is a full scan + filesort of every design on
 *    every page (~230ms); with it MyISAM reads the page straight from the index
 *    in reverse order, no sort (~0.3ms). id is the second column so it doubles
 *    as the deterministic tiebreaker for rows sharing a created_at.
 *
 * 2. (deleted_at) — every page also runs a pagination COUNT(*). MyISAM's cached
 *    row count only applies to an unfiltered COUNT(*); the soft-delete
 *    `WHERE deleted_at IS NULL` disables it and forces a full scan (~200ms).
 *    This index turns the count into a covering index scan (~10ms).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->index(['created_at', 'id'], 'designs_created_at_id_index');
            $table->index('deleted_at', 'designs_deleted_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->dropIndex('designs_created_at_id_index');
            $table->dropIndex('designs_deleted_at_index');
        });
    }
};
