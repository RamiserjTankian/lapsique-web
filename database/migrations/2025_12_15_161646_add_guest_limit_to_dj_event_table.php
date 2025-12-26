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
        Schema::table('dj_event', function (Blueprint $table) {
            $table->unsignedInteger('guest_limit')->nullable()->after('time_slot')->comment('Límite de invitados para este DJ en este evento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dj_event', function (Blueprint $table) {
            $table->dropColumn('guest_limit');
        });
    }
};
