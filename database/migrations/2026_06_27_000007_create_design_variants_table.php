<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_id')->constrained('designs')->restrictOnDelete();
            $table->string('size', 40);
            $table->string('finish', 60);
            $table->string('thickness', 40);
            $table->decimal('purchase_rate', 12, 2);
            $table->decimal('sell_rate', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('design_id');
            $table->unique(['design_id', 'size', 'finish', 'thickness']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_variants');
    }
};
