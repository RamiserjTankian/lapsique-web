<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_event_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('last_ticket_order_id')->nullable()->constrained('ticket_orders')->nullOnDelete();
            $table->string('currency', 3)->default('MXN');
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('total_credited', 10, 2)->default(0);
            $table->decimal('total_consumed', 10, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_event_balances');
    }
};
