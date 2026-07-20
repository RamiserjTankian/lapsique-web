<?php

namespace Tests\Feature;

use App\Models\BookingSlot;
use App\Models\ContentBooking;
use App\Models\PaymentGatewayConnection;
use App\Services\ContentBookingPaymentService;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ElectronicEventCoverageBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.timezone' => 'America/Cancun',
            'booking.electronic_event_coverage_price' => 4500,
        ]);
    }

    public function test_event_coverage_catalog_has_authoritative_price_and_deliverables(): void
    {
        $booking = new ContentBooking([
            'service_type' => ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE,
            'client_name' => 'Promotora Test',
            'amount' => 4500,
            'currency' => 'MXN',
        ]);

        $this->assertSame(4500, ContentBooking::amountForService(ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE));
        $this->assertSame(
            'Cobertura de evento electrónico',
            ContentBooking::serviceOptions()[ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE],
        );
        $this->assertTrue($booking->isElectronicEventCoverage());
        $this->assertSame('Cobertura audiovisual de evento electrónico', $booking->service_name);
        $this->assertSame('Cobertura de evento', $booking->service_short_name);
        $this->assertStringContainsString('aftermovie', $booking->service_description);
        $this->assertStringContainsString('tomas aéreas con dron', $booking->service_description);
        $this->assertStringContainsString('30 fotografías editadas desde distintos ángulos', $booking->service_description);
        $this->assertSame('🎬 Cobertura de evento electrónico — Promotora Test', $booking->service_calendar_summary);
    }

    public function test_event_coverage_checkout_ignores_spoofed_product_and_charges_4500_mxn(): void
    {
        config([
            'stripe.secret_key' => 'sk_test_event_coverage',
            'booking.skip_payment_hosts' => [],
            'booking.skip_payment_host_suffixes' => [],
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_event_coverage',
                'url' => 'https://checkout.stripe.com/pay/cs_event_coverage',
                'status' => 'open',
            ]),
        ]);

        $slot = $this->createAvailableSlot();

        $this->post(route('electronic-event-coverage.checkout'), [
            'booking_slot_id' => $slot->id,
            'client_name' => 'Promotora Test',
            'client_email' => 'eventos@example.com',
            'client_phone' => '529841234567',
            'terms_accepted' => true,
            'payment_provider' => 'mercadopago',
            'service_type' => ContentBooking::SERVICE_CONTENT_SESSION,
            'amount' => 1,
            'currency' => 'USD',
            'product_name' => 'Producto manipulado',
        ])->assertRedirect('https://checkout.stripe.com/pay/cs_event_coverage');

        $booking = ContentBooking::query()->firstOrFail();

        $this->assertSame(ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE, $booking->service_type);
        $this->assertSame(4500, $booking->amount);
        $this->assertSame('MXN', $booking->currency);
        $this->assertSame('stripe', $booking->payment_provider);
        $this->assertSame('cs_event_coverage', $booking->stripe_checkout_session_id);
        $this->assertSame(ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE, data_get($booking->metadata, 'service_type'));
        $this->assertSame(1, $slot->fresh()->booked_count);

        Http::assertSent(function ($request) use ($booking): bool {
            $payload = $request->data();

            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && data_get($payload, 'client_reference_id') === 'bkg_'.$booking->public_id
                && data_get($payload, 'customer_email') === 'eventos@example.com'
                && data_get($payload, 'line_items.0.price_data.currency') === 'mxn'
                && data_get($payload, 'line_items.0.price_data.unit_amount') === 450000
                && data_get($payload, 'line_items.0.price_data.product_data.name') === 'Cobertura de evento electrónico — aftermovie, dron y 30 fotos desde distintos ángulos'
                && str_contains((string) data_get($payload, 'line_items.0.price_data.product_data.description'), '30 fotografías editadas desde distintos ángulos')
                && data_get($payload, 'metadata.content_booking_id') === (string) $booking->id
                && data_get($payload, 'metadata.content_booking_public_id') === $booking->public_id
                && data_get($payload, 'metadata.service_type') === ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE
                && data_get($payload, 'payment_intent_data.metadata.content_booking_id') === (string) $booking->id
                && data_get($payload, 'payment_intent_data.metadata.content_booking_public_id') === $booking->public_id
                && data_get($payload, 'payment_intent_data.metadata.service_type') === ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE;
        });
    }

    public function test_event_coverage_checkout_is_not_available_on_trascendental(): void
    {
        config(['trascendental.enabled_as_primary' => true]);
        Http::fake();

        $this->get(route('electronic-event-coverage.show'))->assertNotFound();

        $this->post(route('electronic-event-coverage.checkout'), [
            'booking_slot_id' => $this->createAvailableSlot()->id,
            'client_name' => 'Promotora Test',
            'client_email' => 'eventos@example.com',
            'client_phone' => '529841234567',
            'terms_accepted' => true,
        ])->assertNotFound();

        $this->assertDatabaseCount('content_bookings', 0);
        Http::assertNothingSent();
    }

    public function test_event_coverage_calendar_event_describes_the_purchased_deliverables(): void
    {
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response([
                'id' => 'gcal_event_coverage',
            ]),
        ]);

        PaymentGatewayConnection::query()->create([
            'provider' => GoogleCalendarService::PROVIDER,
            'status' => 'connected',
            'access_token' => 'google-test-token',
            'token_type' => 'Bearer',
            'expires_at' => now()->addHour(),
            'connected_at' => now(),
        ]);

        $slot = $this->createAvailableSlot();
        $booking = ContentBooking::query()->create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'service_type' => ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE,
            'client_name' => 'Promotora Test',
            'client_email' => 'eventos@example.com',
            'client_phone' => '529841234567',
            'amount' => 4500,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'payment_provider' => 'stripe',
        ])->load('slot');

        $eventId = app(GoogleCalendarService::class)->createBookingEvent($booking);

        $this->assertSame('gcal_event_coverage', $eventId);

        Http::assertSent(function ($request) use ($booking): bool {
            if (! str_contains($request->url(), '/calendar/v3/calendars/primary/events')) {
                return false;
            }

            $payload = $request->data();
            $description = (string) data_get($payload, 'description');

            return data_get($payload, 'summary') === '🎬 Cobertura de evento electrónico — Promotora Test'
                && str_contains($description, 'Servicio: Cobertura audiovisual de evento electrónico')
                && str_contains($description, 'aftermovie, tomas aéreas con dron cuando la operación sea viable y 30 fotografías editadas desde distintos ángulos')
                && str_contains($description, 'Monto: $4,500 MXN')
                && str_contains($description, 'Reserva ID: '.$booking->public_id)
                && data_get($payload, 'start.timeZone') === 'America/Cancun'
                && data_get($payload, 'end.timeZone') === 'America/Cancun';
        });
    }

    public function test_payment_sync_rejects_a_stripe_session_from_another_event_booking(): void
    {
        $booking = ContentBooking::query()->create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $this->createAvailableSlot()->id,
            'service_type' => ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE,
            'client_name' => 'Promotora Test',
            'client_email' => 'eventos@example.com',
            'client_phone' => '529841234567',
            'amount' => 4500,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_event_expected',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no corresponde a esta reserva');

        app(ContentBookingPaymentService::class)->syncStripeSession($booking, [
            'id' => 'cs_event_other',
            'client_reference_id' => 'bkg_'.Str::uuid(),
            'amount_total' => 450000,
            'currency' => 'mxn',
            'payment_status' => 'paid',
            'metadata' => [
                'content_booking_id' => '999999',
                'content_booking_public_id' => (string) Str::uuid(),
                'service_type' => ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE,
            ],
        ]);
    }

    public function test_payment_sync_confirms_only_a_matching_event_coverage_session(): void
    {
        Bus::fake();

        $slot = $this->createAvailableSlot();
        $slot->update(['booked_count' => 1]);

        $booking = ContentBooking::query()->create([
            'public_id' => (string) Str::uuid(),
            'booking_slot_id' => $slot->id,
            'service_type' => ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE,
            'client_name' => 'Promotora Test',
            'client_email' => 'eventos@example.com',
            'client_phone' => '529841234567',
            'amount' => 4500,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_event_matching',
        ]);

        $updated = app(ContentBookingPaymentService::class)->syncStripeSession($booking, [
            'id' => 'cs_event_matching',
            'client_reference_id' => 'bkg_'.$booking->public_id,
            'amount_total' => 450000,
            'currency' => 'mxn',
            'payment_status' => 'paid',
            'payment_intent' => [
                'id' => 'pi_event_matching',
                'status' => 'succeeded',
            ],
            'metadata' => [
                'content_booking_id' => (string) $booking->id,
                'content_booking_public_id' => $booking->public_id,
                'service_type' => ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE,
            ],
        ]);

        $this->assertSame('confirmed', $updated->status);
        $this->assertSame('pi_event_matching', $updated->stripe_payment_intent_id);
        $this->assertSame('succeeded', $updated->stripe_status);
        $this->assertNotNull($updated->paid_at);
        $this->assertSame(1, $slot->fresh()->booked_count);
    }

    protected function createAvailableSlot(): BookingSlot
    {
        return BookingSlot::query()->create([
            'date' => now()->addDays(3)->toDateString(),
            'time_label' => '2:00 PM',
            'time_value' => '14:00',
            'max_bookings' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);
    }
}
