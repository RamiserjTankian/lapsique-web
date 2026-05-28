<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->string('client_ip_address', 45)->nullable()->after('landing_url');
            $table->text('client_user_agent')->nullable()->after('client_ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->dropColumn(['client_ip_address', 'client_user_agent']);
        });
    }
};
