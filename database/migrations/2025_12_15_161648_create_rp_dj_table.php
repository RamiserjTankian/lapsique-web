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
        Schema::create('rp_dj', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rp_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dj_id')->constrained()->cascadeOnDelete();
            $table->unique(['rp_id', 'dj_id']);
            $table->timestamps();
            
            $table->index('rp_id');
            $table->index('dj_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rp_dj');
    }
};
