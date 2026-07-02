<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('order_rooms')->cascadeOnDelete();
            $table->foreignId('design_variant_id')->constrained('design_variants')->restrictOnDelete();
            $table->string('product_image_path', 255)->nullable();

            $table->enum('item_type', ['box', 'piece'])->index();
            // Boxes ordered (box items) or pieces ordered (piece items); always set.
            $table->unsignedInteger('quantity');
            // Pieces contained in one box — box items only; null for piece items.
            $table->unsignedInteger('pieces_per_box')->nullable();

            $table->enum('measurement_unit', ['mm', 'inch', 'feet']);
            $table->decimal('height', 10, 3);
            $table->decimal('width', 10, 3);
            $table->decimal('sqft_rate', 12, 2);
            // Per-piece price (area × sqft_rate, salesman-overridable).
            $table->decimal('price_per_item', 14, 2);
            // Line total, derived server-side: price_per_item × quantity (× pieces_per_box for box items).
            $table->decimal('product_total', 14, 2);

            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('room_id');
            $table->index('design_variant_id');
            $table->index(['room_id', 'sort_order']);
        });

        // quantity is always required (NOT NULL column). pieces_per_box is
        // required for box items and must be absent for piece items.
        DB::statement("
            ALTER TABLE `order_items`
            ADD CONSTRAINT `chk_order_items_type_fields` CHECK (
                (item_type = 'box'   AND pieces_per_box IS NOT NULL)
             OR (item_type = 'piece' AND pieces_per_box IS NULL)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
