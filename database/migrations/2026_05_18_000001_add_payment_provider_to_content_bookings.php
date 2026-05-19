<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->string('payment_provider', 30)->default('mercadopago')->after('status');
            $table->string('stripe_checkout_session_id')->nullable()->after('mercadopago_status');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            $table->string('stripe_status')->nullable()->after('stripe_payment_intent_id');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('booking_calendar_notify_email')->nullable()->after('google_calendar_id');
            $table->string('booking_studio_location')->nullable()->after('booking_calendar_notify_email');
        });
    }

    public function down(): void
    {
        Schema::table('content_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_provider',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'stripe_status',
            ]);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['booking_calendar_notify_email', 'booking_studio_location']);
        });
    }
};
