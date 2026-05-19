<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('booking_team_name')->nullable()->after('booking_whatsapp');
            $table->text('booking_team_bio')->nullable()->after('booking_team_name');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['booking_team_name', 'booking_team_bio']);
        });
    }
};
