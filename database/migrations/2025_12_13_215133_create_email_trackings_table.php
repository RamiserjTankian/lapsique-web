<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            
            // Token único para tracking
            $table->uuid('tracking_token')->unique()->index();
            
            // Contadores
            $table->integer('opens_count')->default(0);
            $table->integer('clicks_count')->default(0);
            
            // Timestamps de primera y última acción
            $table->timestamp('first_opened_at')->nullable()->index();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('first_clicked_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
            
            // URLs clickeadas
            $table->json('clicked_links')->nullable()->comment('Array de URLs clickeadas con timestamps');
            
            // Información del dispositivo/navegador
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('location')->nullable()->comment('Geolocalización basada en IP');
            $table->enum('device_type', ['desktop', 'mobile', 'tablet', 'unknown'])->default('unknown');
            
            $table->timestamps();
            
            // Índices
            $table->index(['customer_id', 'first_opened_at']);
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_trackings');
    }
};
