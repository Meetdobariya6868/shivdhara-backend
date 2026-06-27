<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Orders — the aggregate root. `user_id` is the creator (admin or salesman).
     * `total_price` is a persisted snapshot computed from the items inside one
     * DB transaction so listing/reporting never re-aggregates child rows.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('architect_id')->nullable()->constrained('architects')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->nullOnDelete();

            $table->enum('order_type', ['Local', 'Architect'])->default('Local');
            $table->unsignedTinyInteger('order_status')->default(0);

            $table->decimal('transportation_charge', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_purchase', 14, 2)->default(0);
            $table->decimal('total_profit', 14, 2)->default(0);
            $table->decimal('total_price', 14, 2)->default(0);

            $table->text('notes')->nullable();
            $table->date('order_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('order_status', 'idx_status');
            $table->index('order_date', 'idx_order_date');
            $table->index(['user_id', 'order_status'], 'idx_user_status');
            $table->index(['customer_id', 'created_at'], 'idx_customer_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
