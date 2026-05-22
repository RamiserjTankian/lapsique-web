<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->string('service_type', 40)
                ->default('content_session')
                ->after('booking_slot_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->dropIndex(['service_type']);
            $table->dropColumn('service_type');
        });
    }
};
