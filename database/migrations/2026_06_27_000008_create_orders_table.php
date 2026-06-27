<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->date('order_date')->index();

            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('order_category_id')->constrained('order_categories')->restrictOnDelete();
            $table->foreignId('order_type_id')->constrained('order_types')->restrictOnDelete();
            $table->foreignId('creator_id')->constrained('users')->restrictOnDelete();

            $table->decimal('advance_payment', 14, 2)->default(0);
            $table->decimal('transportation_charge', 14, 2)->default(0);
            $table->text('notes')->nullable();

            $table->decimal('total_purchase_amount', 14, 2)->default(0);
            $table->decimal('total_sell_amount', 14, 2)->default(0);
            $table->decimal('total_profit', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('balance_due', 14, 2)->default(0);

            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('order_category_id');
            $table->index('order_type_id');
            $table->index('creator_id');
            $table->index(['creator_id', 'order_date']);
            $table->index(['order_date', 'order_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
