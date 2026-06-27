<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('room_name', 80);
            $table->integer('sort_order')->default(0);

            $table->decimal('total_sqft', 14, 4)->default(0);
            $table->decimal('total_purchase', 14, 2)->default(0);
            $table->decimal('total_sell', 14, 2)->default(0);
            $table->decimal('total_profit', 14, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('order_id');
            $table->index(['order_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_rooms');
    }
};
