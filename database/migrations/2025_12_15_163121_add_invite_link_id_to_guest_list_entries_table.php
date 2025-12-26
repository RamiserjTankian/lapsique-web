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
            $table->foreignId('invite_link_id')->nullable()->after('rp_id')->constrained('guest_list_invite_links')->nullOnDelete();
            $table->index('invite_link_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_list_entries', function (Blueprint $table) {
            $table->dropForeign(['invite_link_id']);
            $table->dropIndex(['guest_list_entries_invite_link_id_index']);
            $table->dropColumn('invite_link_id');
        });
    }
};
