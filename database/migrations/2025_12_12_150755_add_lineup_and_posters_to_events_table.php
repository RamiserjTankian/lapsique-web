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
            $table->string('featured_poster')->default('horizontal');
            $table->boolean('has_vertical_poster')->default(false);
            $table->boolean('has_horizontal_poster')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'featured_poster',
                'has_vertical_poster',
                'has_horizontal_poster',
            ]);
        });
    }
};
