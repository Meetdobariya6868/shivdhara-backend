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
            $table->unsignedInteger('pieces_per_box')->nullable();
            $table->unsignedInteger('number_of_boxes')->nullable();
            $table->unsignedInteger('number_of_pieces')->nullable();

            $table->enum('measurement_unit', ['mm', 'inch', 'feet']);
            $table->decimal('height', 10, 3);
            $table->decimal('width', 10, 3);
            $table->decimal('sqft_rate', 12, 2);
            $table->unsignedInteger('total_pieces');
            $table->decimal('purchase_amount', 14, 2);
            $table->decimal('sell_amount', 14, 2);
            $table->decimal('product_total', 14, 2);

            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('room_id');
            $table->index('design_variant_id');
            $table->index(['room_id', 'sort_order']);
        });

        DB::statement("
            ALTER TABLE `order_items`
            ADD CONSTRAINT `chk_order_items_type_fields` CHECK (
                (item_type = 'box'   AND pieces_per_box IS NOT NULL AND number_of_boxes IS NOT NULL AND number_of_pieces IS NULL)
             OR (item_type = 'piece' AND number_of_pieces IS NOT NULL AND pieces_per_box IS NULL AND number_of_boxes IS NULL)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
