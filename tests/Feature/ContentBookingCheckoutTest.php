<?php

namespace Tests\Feature;

use App\Jobs\SendContentBookingConfirmationEmailJob;
use App\Jobs\SendContentBookingReceiptEmailJob;
use App\Jobs\SendCustomerPortalAccessEmailJob;
use App\Models\BookingSlot;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\SiteSetting;
use App\Services\ContentBookingPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContentBookingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createAvailableSlot(): BookingSlot
    {
        return BookingSlot::create([
            'date' => now()->addDays(3)->toDateString(),
            'time_label' => '10:00 AM',
            'time_value' => '10:00',
            'max_bookings' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);
    }

    protected function checkoutPayload(BookingSlot $slot): array
    {
        return [
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente Test',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'client_instagram' => '@cliente',
        ];
    }

    public function test_home_includes_agenda_section(): void
    {
        SiteSetting::query()->create([
            'booking_price' => 5000,
            'booking_title' => 'Sesión Test',
        ]);

        $this->createAvailableSlot();

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->where('title', 'Sesión Test')
                ->has('slots', 1)
            );
    }

    public function test_checkout_with_mercadopago_redirects_to_preference(): void
    {
        config(['mercadopago.access_token' => 'TEST-token']);

        Http::fake([
            '*mercadopago.com*' => Http::response([
                'id' => 'pref_test_123',
                'init_point' => 'https://www.mercadopago.com.mx/checkout/v1/redirect?pref_id=pref_test_123',
            ], 201),
        ]);

        $slot = $this->createAvailableSlot();

        $response = $this->post(route('booking.checkout'), array_merge($this->checkoutPayload($slot), [
            'payment_provider' => 'mercadopago',
        ]));

        $response->assertRedirect('https://www.mercadopago.com.mx/checkout/v1/redirect?pref_id=pref_test_123');

        $booking = ContentBooking::first();
        $this->assertNotNull($booking);
        $this->assertSame('mercadopago', $booking->payment_provider);
        $this->assertSame('pref_test_123', $booking->mercadopago_preference_id);
        $this->assertSame(1, $slot->fresh()->booked_count);
    }

    public function test_checkout_with_stripe_redirects_to_checkout_session(): void
    {
        config(['stripe.secret_key' => 'sk_test_fake']);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/pay/cs_test_123',
                'status' => 'open',
            ], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $response = $this->post(route('booking.checkout'), array_merge($this->checkoutPayload($slot), [
            'payment_provider' => 'stripe',
        ]));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        $booking = ContentBooking::first();
        $this->assertSame('stripe', $booking->payment_provider);
        $this->assertSame('cs_test_123', $booking->stripe_checkout_session_id);
    }

    public function test_confirm_does_not_confirm_without_payment(): void
    {
        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'mercadopago',
        ]);

        $this->get(route('booking.confirm', $booking->public_id))
            ->assertOk()
            ->assertSee('Estamos confirmando tu pago');

        $this->assertSame('pending_payment', $booking->fresh()->status);
    }

    public function test_mercadopago_webhook_confirms_booking(): void
    {
        config(['mercadopago.access_token' => 'TEST-token', 'mercadopago.webhook_secret' => '']);

        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'mercadopago',
        ]);

        Http::fake([
            'https://api.mercadopago.com/v1/payments/999' => Http::response([
                'id' => 999,
                'status' => 'approved',
                'external_reference' => 'bkg_'.$booking->public_id,
            ], 200),
        ]);

        $this->postJson(route('webhooks.mercadopago').'?topic=payment&id=999')
            ->assertNoContent();

        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame('approved', $booking->fresh()->mercadopago_status);
    }

    public function test_stripe_webhook_confirms_booking(): void
    {
        config(['stripe.webhook_secret' => '']);

        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
        ]);

        $payload = [
            'id' => 'evt_test_stripe_confirm',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test',
                    'client_reference_id' => 'bkg_'.$booking->public_id,
                    'payment_status' => 'paid',
                    'payment_intent' => ['id' => 'pi_test', 'status' => 'succeeded'],
                    'metadata' => [
                        'content_booking_public_id' => $booking->public_id,
                    ],
                ],
            ],
        ];

        $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload),
        )->assertNoContent();

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('cs_test', $booking->stripe_checkout_session_id);
        $this->assertNotNull($booking->paid_at);
    }

    public function test_stripe_webhook_is_idempotent(): void
    {
        config(['stripe.webhook_secret' => '']);

        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
        ]);

        $payload = [
            'id' => 'evt_idempotent_test',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_dup',
                    'client_reference_id' => 'bkg_'.$booking->public_id,
                    'payment_status' => 'paid',
                    'payment_intent' => ['id' => 'pi_dup', 'status' => 'succeeded'],
                ],
            ],
        ];

        $body = json_encode($payload);

        $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body,
        )->assertNoContent();

        $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body,
        )->assertNoContent();

        $this->assertSame(1, \App\Models\StripeWebhookEvent::where('event_id', 'evt_idempotent_test')->count());
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_retry_payment_for_pending_booking(): void
    {
        config(['stripe.secret_key' => 'sk_test_fake']);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_retry_123',
                'url' => 'https://checkout.stripe.com/pay/cs_retry_123',
                'status' => 'open',
            ], 200),
        ]);

        $slot = $this->createAvailableSlot();

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'pending',
            'payment_provider' => 'stripe',
        ]);

        $this->post(route('booking.retry', $booking->public_id))
            ->assertRedirect('https://checkout.stripe.com/pay/cs_retry_123');
    }

    public function test_confirmed_booking_dispatches_post_payment_emails(): void
    {
        Bus::fake([
            SendContentBookingReceiptEmailJob::class,
            SendContentBookingConfirmationEmailJob::class,
            SendCustomerPortalAccessEmailJob::class,
        ]);

        $slot = $this->createAvailableSlot();

        $customer = Customer::create([
            'name' => 'Cliente Email',
            'email' => 'emails@example.com',
            'status' => 'prospect',
        ]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'customer_id' => $customer->id,
            'client_name' => 'Cliente Email',
            'client_email' => 'emails@example.com',
            'client_phone' => '529841234567',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
        ]);

        app(ContentBookingPaymentService::class)->applyStatusTransition($booking, 'confirmed', [
            'source' => 'test',
        ]);

        Bus::assertDispatched(SendContentBookingReceiptEmailJob::class);
        Bus::assertDispatched(SendContentBookingConfirmationEmailJob::class);
        Bus::assertDispatched(SendCustomerPortalAccessEmailJob::class);

        $booking->refresh();
        $this->assertTrue(data_get($booking->metadata, 'receipt_email_sent'));
        $this->assertTrue(data_get($booking->metadata, 'confirmation_email_sent'));
        $this->assertTrue(data_get($booking->metadata, 'portal_access_email_sent'));
        $this->assertSame('customer', $customer->fresh()->status);
    }

    public function test_payment_service_releases_slot_on_failure(): void
    {
        $slot = $this->createAvailableSlot();
        $slot->update(['booked_count' => 1]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'mercadopago',
        ]);

        app(ContentBookingPaymentService::class)->releaseSlotIfFailed($booking, 'failed');

        $this->assertSame('failed', $booking->fresh()->status);
        $this->assertSame(0, $slot->fresh()->booked_count);
    }
}
