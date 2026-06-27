<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Manufacturers / brands whose products appear in the catalog.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('short_code', 20)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 15)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('short_code', 'idx_short_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
