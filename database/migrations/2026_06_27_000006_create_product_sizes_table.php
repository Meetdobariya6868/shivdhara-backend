<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Standard product dimensions. `area_sqft` is a cached conversion of
     * width × height (in `unit`) to square feet, used as the default for
     * pricing; per-order items may still override the measurement.
     */
    public function up(): void
    {
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('label', 30)->unique();
            $table->decimal('width_value', 10, 2);
            $table->decimal('height_value', 10, 2);
            $table->string('unit', 10)->default('inch'); // inch | feet | mm | cm
            $table->decimal('area_sqft', 10, 4)->nullable();
            $table->timestamps();

            $table->index(['width_value', 'height_value', 'unit'], 'idx_dimensions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sizes');
    }
};
