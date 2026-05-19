<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dj_event', function (Blueprint $table) {
            $table->foreignId('b2b_with_dj_id')
                ->nullable()
                ->after('guest_limit')
                ->constrained('djs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dj_event', function (Blueprint $table) {
            $table->dropForeign(['b2b_with_dj_id']);
            $table->dropColumn('b2b_with_dj_id');
        });
    }
};
