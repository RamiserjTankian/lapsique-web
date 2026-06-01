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
        Schema::create('contact_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable();
            $table->foreignId('automation_id')->nullable();
            
            // Canal y tipo de comunicación
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'call', 'popup', 'guestlist', 'manual'])
                  ->index();
            $table->enum('type', ['notification', 'marketing', 'transactional', 'reminder', 'followup'])
                  ->index();
            
            // Contenido
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable()->comment('Datos específicos del canal');
            
            // Estado y tracking
            $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed'])
                  ->default('pending')
                  ->index();
            
            // Timestamps de eventos
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            // Error tracking
            $table->text('error_message')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Índices compuestos para queries comunes
            $table->index(['customer_id', 'channel', 'status']);
            $table->index(['event_id', 'channel']);
            $table->index(['campaign_id', 'status']);
            $table->index(['created_at', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_logs');
    }
};
