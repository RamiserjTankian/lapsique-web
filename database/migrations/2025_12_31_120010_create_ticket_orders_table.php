<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rp_id')->nullable()->constrained('rps')->nullOnDelete();
            $table->foreignId('invite_link_id')->nullable()->constrained('guest_list_invite_links')->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->string('currency', 3)->default('MXN');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->unsignedInteger('items_quantity')->default(0);
            $table->unsignedInteger('attendees_expected')->default(0);
            $table->unsignedInteger('attendees_registered')->default(0);

            $table->string('buyer_name')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_whatsapp')->nullable();
            $table->string('buyer_instagram')->nullable();

            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();

            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->string('mp_preference_id')->nullable();
            $table->string('mp_payment_id')->nullable();
            $table->string('mp_status')->nullable();
            $table->string('mp_status_detail')->nullable();
            $table->string('mp_payment_method')->nullable();
            $table->string('mp_merchant_order_id')->nullable();
            $table->string('mp_external_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'status']);
            $table->index(['buyer_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_orders');
    }
};
