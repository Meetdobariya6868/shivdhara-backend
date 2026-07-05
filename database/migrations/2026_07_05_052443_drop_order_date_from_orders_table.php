<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes `orders.order_date`. It duplicated `created_at` (every order sets it
 * to "today" at creation time) and could drift from it — e.g. seeded/demo rows
 * backdating order_date while created_at stayed "now". `created_at` is now the
 * single source of truth for an order's date, everywhere (filters, display).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['order_date']);
            $table->dropIndex(['creator_id', 'order_date']);
            $table->dropIndex(['order_date', 'order_category_id']);
            $table->dropColumn('order_date');

            $table->index('created_at');
            $table->index(['creator_id', 'created_at']);
            $table->index(['created_at', 'order_category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['creator_id', 'created_at']);
            $table->dropIndex(['created_at', 'order_category_id']);

            // Best-effort only — original order_date values are not recoverable.
            $table->date('order_date')->nullable();
        });
    }
};
