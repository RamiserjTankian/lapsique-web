<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guest_list_entries', function (Blueprint $table) {
            $table->string('invite_token', 64)->nullable()->unique()->after('rp_id')->comment('Token único para link de invitación por DJ');
            $table->index('invite_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_list_entries', function (Blueprint $table) {
            $table->dropIndex(['guest_list_entries_invite_token_index']);
            $table->dropColumn('invite_token');
        });
    }
};
