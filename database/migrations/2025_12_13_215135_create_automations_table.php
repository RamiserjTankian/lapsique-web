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
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Configuración del trigger
            $table->enum('trigger_type', [
                'signup',
                'event_registration',
                'event_reminder',
                'abandoned_cart',
                'birthday',
                'anniversary',
                'tag_added',
                'lifecycle_change',
                'score_threshold'
            ])->index();
            $table->json('trigger_config')->nullable()->comment('Configuración específica del trigger');
            
            // Estado
            $table->enum('status', ['active', 'paused', 'archived'])
                  ->default('active')
                  ->index();
            
            // Flujo de acciones
            $table->json('steps')->comment('Array de pasos del flujo automatizado');
            
            // Métricas
            $table->integer('total_triggered')->default(0);
            $table->integer('total_completed')->default(0);
            $table->integer('total_failed')->default(0);
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Índices
            $table->index(['trigger_type', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automations');
    }
};
