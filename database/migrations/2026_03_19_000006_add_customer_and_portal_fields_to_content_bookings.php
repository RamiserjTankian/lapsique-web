<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('booking_slot_id')
                ->constrained('customers')
                ->nullOnDelete();
            $table->string('shoot_location')->nullable()->after('notes');
            $table->text('admin_notes')->nullable()->after('metadata');
            $table->timestamp('deliverables_ready_at')->nullable()->after('admin_notes');

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn([
                'shoot_location',
                'admin_notes',
                'deliverables_ready_at',
            ]);
        });
    }
};
