<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_order_id')->constrained('ticket_orders')->cascadeOnDelete();
            $table->foreignId('ticket_order_item_id')->constrained('ticket_order_items')->cascadeOnDelete();
            $table->foreignId('ticket_product_id')->constrained('ticket_products')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rp_id')->nullable()->constrained('rps')->nullOnDelete();
            $table->foreignId('invite_link_id')->nullable()->constrained('guest_list_invite_links')->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('instagram_handle')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender', 20)->nullable();
            $table->text('notes')->nullable();
            $table->string('invite_token', 64)->unique();
            $table->timestamp('check_in_at')->nullable();
            $table->unsignedInteger('check_in_limit')->default(1);
            $table->unsignedInteger('check_in_count')->default(0);
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_attendees');
    }
};
