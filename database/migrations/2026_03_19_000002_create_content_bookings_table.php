<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('booking_slot_id')->nullable()->constrained('booking_slots')->nullOnDelete();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone', 30);
            $table->string('client_instagram')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('amount')->default(5000);
            $table->string('currency', 3)->default('MXN');
            $table->string('status', 30)->default('pending_payment');
            $table->string('mercadopago_preference_id')->nullable();
            $table->string('mercadopago_payment_id')->nullable();
            $table->string('mercadopago_status')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('analytics_visitor_id')->nullable();
            $table->string('analytics_session_id')->nullable();
            $table->string('fbp')->nullable();
            $table->string('fbc')->nullable();
            $table->string('referrer')->nullable();
            $table->string('landing_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('client_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_bookings');
    }
};
