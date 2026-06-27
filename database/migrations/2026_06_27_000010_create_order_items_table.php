<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Order line items. Each line references a catalog product (`design_code_id`)
     * for its specs, but captures its own measurement, quantity and PRICE
     * SNAPSHOT (cost & sale) so an issued order stays immutable even if catalog
     * prices change later. `total_pieces`, `total_sqft`, `line_total`,
     * `cost_total` and `profit_amount` are persisted results of the calculation
     * logic (which lives in the domain/service layer).
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('design_code_id')->nullable()->constrained('design_codes')->cascadeOnUpdate()->nullOnDelete();

            $table->unsignedSmallInteger('item_index')->default(0);
            $table->string('area_name', 150)->nullable(); // room / area
            $table->string('custom_name', 200)->nullable();

            // Quantity model
            $table->unsignedTinyInteger('items_type')->default(0); // 0 = box, 1 = piece
            $table->decimal('quantity', 10, 2)->default(0);        // boxes or pieces
            $table->decimal('piece_per_box', 8, 2)->default(0);
            $table->decimal('total_pieces', 12, 2)->default(0);    // derived

            // Measurement → area
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->enum('size_unit', ['mm', 'cm', 'inch', 'feet'])->default('feet');
            $table->decimal('area_sqft', 14, 4)->default(0);  // derived per piece
            $table->decimal('total_sqft', 14, 4)->default(0); // derived

            // Pricing snapshots (per sq ft) + derived totals
            $table->decimal('sqft_rate', 12, 2)->default(0);   // sale rate
            $table->decimal('cost_rate', 12, 2)->default(0);   // purchase rate
            $table->decimal('unit_price', 12, 2)->default(0);  // optional per-piece price
            $table->decimal('cost_total', 14, 2)->default(0);  // derived
            $table->decimal('line_total', 14, 2)->default(0);  // derived (sale)
            $table->decimal('profit_amount', 14, 2)->default(0); // derived

            $table->string('photo_override', 500)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'item_index'], 'idx_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
