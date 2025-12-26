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
            $table->foreignId('dj_id')->nullable()->after('event_id')->constrained()->nullOnDelete()->comment('DJ que invitó a este cliente');
            $table->foreignId('rp_id')->nullable()->after('dj_id')->constrained('rps')->nullOnDelete()->comment('RP que gestionó esta invitación');
            
            $table->index(['dj_id', 'event_id']);
            $table->index(['rp_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_list_entries', function (Blueprint $table) {
            $table->dropForeign(['dj_id']);
            $table->dropForeign(['rp_id']);
            $table->dropIndex(['guest_list_entries_dj_id_event_id_index']);
            $table->dropIndex(['guest_list_entries_rp_id_event_id_index']);
            $table->dropColumn(['dj_id', 'rp_id']);
        });
    }
};
