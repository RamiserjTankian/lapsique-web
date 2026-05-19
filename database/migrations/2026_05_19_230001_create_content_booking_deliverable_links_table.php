<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('content_booking_deliverable_links')) {
            return;
        }

        Schema::create('content_booking_deliverable_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_booking_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('url', 2048);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['content_booking_id', 'created_at'], 'cb_deliverable_links_booking_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_booking_deliverable_links');
    }
};
