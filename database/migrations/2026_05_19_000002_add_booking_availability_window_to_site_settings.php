<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('booking_availability_days')->default(11)->after('booking_weeks_ahead');
            $table->time('booking_start_time')->default('14:00')->after('booking_availability_days');
            $table->time('booking_end_time')->default('17:00')->after('booking_start_time');
        });

        DB::table('site_settings')->update([
            'booking_availability_days' => 11,
            'booking_start_time' => '14:00',
            'booking_end_time' => '17:00',
            'booking_duration_minutes' => 120,
        ]);
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'booking_availability_days',
                'booking_start_time',
                'booking_end_time',
            ]);
        });
    }
};
