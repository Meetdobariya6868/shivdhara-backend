<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Product catalog. A "design code" is a concrete sellable product: a design
     * from a company, in a given size/finish/thickness, with default purchase &
     * sale prices and packing info. Order items reference a row here, which
     * keeps product attributes normalized (stored once) rather than repeated on
     * every order line.
     */
    public function up(): void
    {
        Schema::create('design_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('size_id')->constrained('product_sizes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('finish_id')->nullable()->constrained('product_finishes')->cascadeOnUpdate()->nullOnDelete();

            $table->string('design_name', 200);
            $table->string('design_code', 20)->unique();
            $table->unsignedSmallInteger('thickness')->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('piece_per_box', 8, 2)->nullable();
            $table->decimal('weight_per_box_kg', 8, 2)->nullable();
            $table->string('photo', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('design_name', 'idx_design_name');
            $table->index(['company_id', 'size_id'], 'idx_company_size');
            $table->index(['is_active', 'category_id'], 'idx_active_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_codes');
    }
};
