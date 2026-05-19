<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_attendee_id')->constrained('ticket_attendees')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scan_status', 30)->index();
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->index(['ticket_attendee_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_scans');
    }
};
