<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('home_hero_proof_1_title')->nullable();
            $table->string('home_hero_proof_1_source')->nullable();
            $table->string('home_hero_proof_1_reference')->nullable();

            $table->string('home_hero_proof_2_title')->nullable();
            $table->string('home_hero_proof_2_source')->nullable();
            $table->string('home_hero_proof_2_reference')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'home_hero_proof_1_title',
                'home_hero_proof_1_source',
                'home_hero_proof_1_reference',
                'home_hero_proof_2_title',
                'home_hero_proof_2_source',
                'home_hero_proof_2_reference',
            ]);
        });
    }
};
