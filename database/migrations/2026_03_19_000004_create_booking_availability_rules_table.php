<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_availability_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week'); // 1=Mon, 2=Tue, ... 7=Sun (ISO)
            $table->string('time_label', 20);           // e.g. "10:00 AM"
            $table->string('time_value', 5);            // e.g. "10:00" (24h for sorting)
            $table->unsignedSmallInteger('max_bookings')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['day_of_week', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_availability_rules');
    }
};
