<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->text('secret_key')->nullable();
            $table->text('publishable_key')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('currency', 3)->default('MXN');
            $table->unsignedSmallInteger('webhook_tolerance_seconds')->default(300);
            $table->string('connection_status', 30)->default('unknown');
            $table->timestamp('last_verified_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->json('last_verification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_settings');
    }
};
