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
        Schema::create('guest_list_invite_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dj_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rp_id')->nullable()->constrained('rps')->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->string('name')->nullable()->comment('Nombre descriptivo del link');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_registrations')->nullable()->comment('Límite de registros');
            $table->unsignedInteger('current_registrations')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['event_id', 'dj_id']);
            $table->index(['event_id', 'rp_id']);
            $table->index('token');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_list_invite_links');
    }
};
