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
            // Eliminar campos duplicados que ahora están en customers
            $table->dropColumn(['full_name', 'email', 'whatsapp', 'instagram_handle', 'accepts_emails']);
            
            // Añadir nuevos campos específicos del evento
            $table->timestamp('check_in_at')->nullable()->after('status');
            $table->string('invited_by')->nullable()->after('gender')->comment('Referral tracking');
            $table->integer('plus_ones')->default(0)->after('invited_by');
            
            // Mejorar el enum de status
            $table->dropColumn('status');
            $table->enum('status', ['pending', 'confirmed', 'attended', 'cancelled', 'no_show'])
                  ->default('pending')
                  ->after('event_id');
            
            // Índices
            $table->index(['event_id', 'status']);
            $table->index('check_in_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_list_entries', function (Blueprint $table) {
            $table->dropIndex(['guest_list_entries_event_id_status_index']);
            $table->dropIndex(['guest_list_entries_check_in_at_index']);
            
            $table->dropColumn(['check_in_at', 'invited_by', 'plus_ones', 'status']);
            
            $table->string('full_name')->after('customer_id');
            $table->string('email')->after('full_name');
            $table->string('whatsapp')->nullable()->after('email');
            $table->string('instagram_handle')->nullable()->after('whatsapp');
            $table->boolean('accepts_emails')->default(false)->after('instagram_handle');
            $table->string('status')->default('pending')->after('notes');
        });
    }
};
