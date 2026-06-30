<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'status')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            // Workflow state of the order. New orders start as "pending" and are
            // moved to "confirmed" by an admin/creator. Indexed for list filters.
            $table->string('status', 20)->default('pending')->after('grand_total');
            $table->index(['creator_id', 'status']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'status')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['creator_id', 'status']);
            $table->dropColumn('status');
        });
    }
};
