<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Customers — a reusable master entity with full billing/contact details
     * (GST number supports tax-compliant invoicing). Phone is indexed for fast
     * lookup but intentionally NOT unique (matches existing business behaviour).
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 15)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->string('photo', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index('name');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
