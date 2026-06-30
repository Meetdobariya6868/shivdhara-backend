<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'architect_name')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            // Captured only when the order's type is "Architect"; null for every
            // other type (e.g. Local, Retailer).
            $table->string('architect_name', 120)->nullable()->after('creator_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'architect_name')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('architect_name');
        });
    }
};
