<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('order_types')->insert([
            ['name' => 'local',    'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'retailer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'architect','is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('order_types');
    }
};
