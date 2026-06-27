<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mobile app release tracking (supports forced-update gating on launch).
     */
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20);
            $table->enum('platform', ['android', 'ios', 'both'])->default('both');
            $table->boolean('is_latest')->default(false);
            $table->boolean('force_update')->default(false);
            $table->text('release_notes')->nullable();
            $table->timestamps();

            $table->index(['platform', 'is_latest'], 'idx_platform_latest');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
