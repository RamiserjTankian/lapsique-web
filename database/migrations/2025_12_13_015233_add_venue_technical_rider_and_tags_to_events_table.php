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
        Schema::table('events', function (Blueprint $table) {
            // Relación con Location (venue)
            $table->foreignId('location_id')->nullable()->after('city')->constrained()->nullOnDelete();
            
            // Technical Rider (CDJs, Mixers, Sound System)
            $table->json('technical_rider')->nullable()->after('location_id');
            
            // Tags generales (Recording Party, etc.)
            $table->json('tags')->nullable()->after('technical_rider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn(['location_id', 'technical_rider', 'tags']);
        });
    }
};
