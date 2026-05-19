<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->string('deliverables_drive_url', 2048)->nullable()->after('deliverables_ready_at');
        });
    }

    public function down(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->dropColumn('deliverables_drive_url');
        });
    }
};
