<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->string('google_calendar_event_id')->nullable()->after('mercadopago_status');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('google_calendar_id')->nullable();        // e.g. "primary" or a specific calendar ID
            $table->unsignedSmallInteger('booking_weeks_ahead')->default(4);
            $table->unsignedSmallInteger('booking_advance_hours')->default(24);
            $table->unsignedSmallInteger('booking_duration_minutes')->default(120);
        });
    }

    public function down(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->dropColumn('google_calendar_event_id');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'google_calendar_id',
                'booking_weeks_ahead',
                'booking_advance_hours',
                'booking_duration_minutes',
            ]);
        });
    }
};
