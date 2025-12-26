<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guest_list_entries', function (Blueprint $table) {
            $table->unsignedInteger('check_in_limit')->default(1)->after('check_in_at');
            $table->unsignedInteger('check_in_count')->default(0)->after('check_in_limit');
        });

        DB::table('guest_list_entries')
            ->whereNotNull('check_in_at')
            ->update(['check_in_count' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_list_entries', function (Blueprint $table) {
            $table->dropColumn(['check_in_limit', 'check_in_count']);
        });
    }
};
