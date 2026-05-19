<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('booking_title')->nullable();
            $table->string('booking_subtitle')->nullable();
            $table->unsignedInteger('booking_price')->default(5000);
            $table->string('booking_whatsapp', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['booking_title', 'booking_subtitle', 'booking_price', 'booking_whatsapp']);
        });
    }
};
