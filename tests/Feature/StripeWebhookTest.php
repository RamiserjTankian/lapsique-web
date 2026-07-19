<?php

namespace Tests\Feature;

use App\Models\BookingSlot;
use App\Models\ContentBooking;
use App\Models\Event;
use App\Models\StripeWebhookEvent;
use App\Models\TicketProduct;
use App\Services\TicketOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected string $webhookSecret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['stripe.webhook_secret' => $this->webhookSecret]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function postStripeWebhook(array $payload): \Illuminate\Testing\TestResponse
    {
        $body = json_encode($payload);

        return $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->stripeSignatureHeader($body, $this->webhookSecret),
            ],
            $body,
        );
    }

    protected function stripeSignatureHeader(string $payload, string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();

        return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    }

    protected function createAvailableSlot(): BookingSlot
    {
        return BookingSlot::create([
            'date' => now()->addDays(3)->toDateString(),
            'time_label' => '2:00 PM',
            'time_value' => '14:00',
            'max_bookings' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);
    }

    public function test_checkout_session_completed_confirms_booking(): void
    {
        $slot = $this->createAvailableSlot();
        $slot->update(['booked_count' => 1]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_existing',
        ]);

        $this->postStripeWebhook([
            'id' => 'evt_booking_paid',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_paid',
                    'client_reference_id' => 'bkg_'.$booking->public_id,
                    'payment_status' => 'paid',
                    'payment_intent' => ['id' => 'pi_test_paid', 'status' => 'succeeded'],
                    'metadata' => [
                        'content_booking_public_id' => $booking->public_id,
                    ],
                ],
            ],
        ])->assertNoContent();

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('cs_test_paid', $booking->stripe_checkout_session_id);
        $this->assertNotNull($booking->paid_at);
    }

    public function test_webhook_is_idempotent_by_event_id(): void
    {
        $slot = $this->createAvailableSlot();
        $slot->update(['booked_count' => 1]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
        ]);

        $payload = [
            'id' => 'evt_idempotent_stripe',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_idem',
                    'client_reference_id' => 'bkg_'.$booking->public_id,
                    'payment_status' => 'paid',
                    'payment_intent' => ['id' => 'pi_idem', 'status' => 'succeeded'],
                ],
            ],
        ];

        $this->postStripeWebhook($payload)->assertNoContent();
        $this->postStripeWebhook($payload)->assertNoContent();

        $this->assertSame(1, StripeWebhookEvent::where('event_id', 'evt_idempotent_stripe')->count());
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_checkout_session_expired_releases_booking_slot(): void
    {
        $slot = $this->createAvailableSlot();
        $slot->update(['booked_count' => 1]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_expired_booking',
        ]);

        $this->postStripeWebhook([
            'id' => 'evt_booking_expired',
            'type' => 'checkout.session.expired',
            'data' => [
                'object' => [
                    'id' => 'cs_expired_booking',
                    'status' => 'expired',
                ],
            ],
        ])->assertNoContent();

        $this->assertSame('failed', $booking->fresh()->status);
        $this->assertSame(0, $slot->fresh()->booked_count);
    }

    public function test_charge_refunded_cancels_confirmed_booking_and_releases_slot(): void
    {
        $slot = $this->createAvailableSlot();
        $slot->update(['booked_count' => 1]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
            'payment_provider' => 'stripe',
            'stripe_payment_intent_id' => 'pi_refund_booking',
        ]);

        $this->postStripeWebhook([
            'id' => 'evt_booking_refund',
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'id' => 'ch_refund_booking',
                    'payment_intent' => 'pi_refund_booking',
                    'status' => 'refunded',
                ],
            ],
        ])->assertNoContent();

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('refunded', $booking->stripe_status);
        $this->assertSame(0, $slot->fresh()->booked_count);
    }

    public function test_checkout_session_completed_marks_ticket_order_paid(): void
    {
        $event = Event::create([
            'title' => 'Stripe Event',
            'slug' => 'stripe-event',
            'starts_at' => now()->addWeek(),
        ]);

        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'General',
            'category' => 'ticket',
            'currency' => 'MXN',
            'price' => 1150,
            'service_charge_pct' => 15,
            'access_units' => 1,
            'check_in_limit' => 1,
            'is_active' => true,
        ]);

        $order = app(TicketOrderService::class)->createOrder(
            $event,
            [$product->id => 1],
            [
                'name' => 'Buyer',
                'email' => 'buyer@example.com',
                'whatsapp' => '9991112233',
                'phone' => '9991112233',
            ],
        );

        $order->update([
            'status' => 'pending',
            'payment_provider' => 'stripe',
            'stripe_session_id' => 'cs_ticket_paid',
        ]);

        $this->postStripeWebhook([
            'id' => 'evt_ticket_paid',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_ticket_paid',
                    'client_reference_id' => $order->public_id,
                    'payment_status' => 'paid',
                    'payment_intent' => ['id' => 'pi_ticket_paid', 'status' => 'succeeded'],
                    'metadata' => [
                        'ticket_order_public_id' => $order->public_id,
                    ],
                ],
            ],
        ])->assertNoContent();

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertGreaterThan(0, $order->attendees()->count());
    }

    public function test_checkout_session_expired_releases_ticket_reservation(): void
    {
        $event = Event::create([
            'title' => 'Stripe Expired',
            'slug' => 'stripe-expired',
            'starts_at' => now()->addWeek(),
        ]);

        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'Limited',
            'category' => 'ticket',
            'currency' => 'MXN',
            'price' => 1000,
            'service_charge_pct' => 0,
            'access_units' => 1,
            'check_in_limit' => 1,
            'stock' => 5,
            'reserved_count' => 0,
            'is_active' => true,
        ]);

        $order = app(TicketOrderService::class)->createOrder(
            $event,
            [$product->id => 1],
            [
                'name' => 'Buyer',
                'email' => 'expired@example.com',
                'whatsapp' => '9991112233',
                'phone' => '9991112233',
            ],
        );

        $order->update([
            'payment_provider' => 'stripe',
            'stripe_session_id' => 'cs_ticket_expired',
        ]);

        $product->refresh();
        $this->assertSame(1, $product->reserved_count);

        $this->postStripeWebhook([
            'id' => 'evt_ticket_expired',
            'type' => 'checkout.session.expired',
            'data' => [
                'object' => [
                    'id' => 'cs_ticket_expired',
                    'client_reference_id' => $order->public_id,
                    'status' => 'expired',
                ],
            ],
        ])->assertNoContent();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(0, $product->fresh()->reserved_count);
    }

    public function test_charge_refunded_rolls_back_paid_ticket_order(): void
    {
        $event = Event::create([
            'title' => 'Stripe Refund',
            'slug' => 'stripe-refund',
            'starts_at' => now()->addWeek(),
        ]);

        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'Stocked',
            'category' => 'ticket',
            'currency' => 'MXN',
            'price' => 1000,
            'service_charge_pct' => 0,
            'access_units' => 1,
            'check_in_limit' => 1,
            'stock' => 10,
            'sold_count' => 0,
            'reserved_count' => 0,
            'is_active' => true,
        ]);

        $order = app(TicketOrderService::class)->createOrder(
            $event,
            [$product->id => 1],
            [
                'name' => 'Buyer',
                'email' => 'refund@example.com',
                'whatsapp' => '9991112233',
                'phone' => '9991112233',
            ],
        );

        app(TicketOrderService::class)->syncStripeSession($order, [
            'id' => 'cs_refund_ticket',
            'payment_status' => 'paid',
            'payment_intent' => ['id' => 'pi_refund_ticket', 'status' => 'succeeded'],
        ]);

        $product->refresh();
        $this->assertSame(1, $product->sold_count);

        $this->postStripeWebhook([
            'id' => 'evt_ticket_refund',
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'id' => 'ch_refund_ticket',
                    'payment_intent' => 'pi_refund_ticket',
                    'status' => 'refunded',
                ],
            ],
        ])->assertNoContent();

        $order->refresh();
        $product->refresh();

        $this->assertSame('refunded', $order->status);
        $this->assertSame(0, $product->sold_count);
    }

    public function test_invalid_webhook_signature_returns_401(): void
    {
        config(['stripe.webhook_secret' => 'whsec_test_secret']);

        $response = $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => 't='.time().',v1=invalid',
            ],
            json_encode([
                'id' => 'evt_bad_sig',
                'type' => 'checkout.session.completed',
                'data' => ['object' => []],
            ]),
        );

        $response->assertStatus(401);
    }

    public function test_webhook_without_configured_secret_returns_401(): void
    {
        config(['stripe.webhook_secret' => '']);

        $payload = json_encode([
            'id' => 'evt_missing_secret',
            'type' => 'checkout.session.completed',
            'data' => ['object' => []],
        ]);

        $response = $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->stripeSignatureHeader($payload, 'whsec_unconfigured'),
            ],
            $payload,
        );

        $response->assertStatus(401);
        $this->assertDatabaseMissing('stripe_webhook_events', ['event_id' => 'evt_missing_secret']);
    }

    public function test_valid_webhook_signature_is_accepted(): void
    {
        config(['stripe.webhook_secret' => 'whsec_test_secret']);

        $payload = json_encode([
            'id' => 'evt_valid_sig',
            'type' => 'checkout.session.completed',
            'data' => ['object' => []],
        ]);

        $response = $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->stripeSignatureHeader($payload, 'whsec_test_secret'),
            ],
            $payload,
        );

        $response->assertNoContent();
    }

    public function test_webhook_signature_accepts_any_valid_v1_signature(): void
    {
        config(['stripe.webhook_secret' => 'whsec_test_secret']);

        $timestamp = time();
        $payload = json_encode([
            'id' => 'evt_multiple_sigs',
            'type' => 'checkout.session.completed',
            'data' => ['object' => []],
        ]);
        $validSignature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test_secret');

        $response = $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$validSignature.',v1=invalid',
            ],
            $payload,
        );

        $response->assertNoContent();
    }
}
