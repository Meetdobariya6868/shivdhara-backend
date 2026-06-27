<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Immutable audit trail of order status transitions (who changed what,
     * when, and why). Append-only; never updated.
     */
    public function up(): void
    {
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedTinyInteger('old_status')->nullable();
            $table->unsignedTinyInteger('new_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('remarks', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at', 'idx_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
