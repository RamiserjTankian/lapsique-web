<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dj_event', function (Blueprint $table) {
            $table->string('role', 20)->default('warmup')->after('event_id');
            $table->unsignedInteger('position')->default(0)->after('role');
            $table->index(['event_id', 'role', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('dj_event', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'role', 'position']);
            $table->dropColumn(['role', 'position']);
        });
    }
};
