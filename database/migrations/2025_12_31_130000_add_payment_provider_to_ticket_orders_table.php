<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->string('payment_provider', 30)->default('mercadopago')->after('status');
            $table->string('stripe_session_id')->nullable()->after('mp_external_reference');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');
            $table->string('stripe_status')->nullable()->after('stripe_payment_intent_id');
            $table->string('stripe_payment_method')->nullable()->after('stripe_status');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_provider',
                'stripe_session_id',
                'stripe_payment_intent_id',
                'stripe_status',
                'stripe_payment_method',
            ]);
        });
    }
};
