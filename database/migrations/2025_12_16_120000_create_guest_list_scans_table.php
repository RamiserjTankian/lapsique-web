<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_list_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_list_entry_id')
                ->constrained('guest_list_entries')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('scan_status');
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->index(['guest_list_entry_id', 'scanned_at']);
            $table->index('scan_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_list_scans');
    }
};
