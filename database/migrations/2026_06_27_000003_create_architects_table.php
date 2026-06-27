<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Architects who refer / place orders on behalf of clients. Linked from
     * orders when order_type = 'Architect'.
     */
    public function up(): void
    {
        Schema::create('architects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 15)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('firm_name', 200)->nullable();
            $table->string('city', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('architects');
    }
};
