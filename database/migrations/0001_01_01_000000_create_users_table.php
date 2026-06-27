<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `users` holds admins and salesmen (and reserved manager/viewer roles),
     * distinguished by `role`. Authentication is by phone number (unique).
     * `is_active` toggles login; `can_create_orders` is the salesman-level
     * permission an admin grants/revokes.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 15)->unique();
            $table->string('email', 150)->nullable();
            $table->string('photo', 500)->nullable();
            $table->string('password');

            $table->string('role', 20)->default('sales');
            $table->boolean('is_active')->default(true);
            $table->boolean('can_create_orders')->default(true);
            $table->unsignedTinyInteger('login_mode')->default(1);
            $table->string('fcm_token')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role', 'is_active'], 'idx_role_active');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone', 15)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
