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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Tipo y estado
            $table->enum('type', ['email', 'sms', 'whatsapp', 'multi_channel'])
                  ->index();
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed'])
                  ->default('draft')
                  ->index();
            
            // Segmentación
            $table->json('target_audience')->nullable()->comment('Filtros de segmentación');
            $table->json('content')->nullable()->comment('Templates y contenido por canal');
            
            // Scheduling
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable();
            
            // Métricas
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('conversion_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->integer('failed_count')->default(0);
            
            // Metadata adicional
            $table->json('metadata')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Índices
            $table->index(['status', 'starts_at']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
