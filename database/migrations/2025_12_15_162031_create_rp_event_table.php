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
        Schema::create('rp_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rp_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission_rate', 5, 2)->nullable()->comment('Comisión específica para este evento');
            $table->text('notes')->nullable()->comment('Notas específicas del RP para este evento');
            $table->unique(['rp_id', 'event_id']);
            $table->timestamps();
            
            $table->index('rp_id');
            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rp_event');
    }
};
