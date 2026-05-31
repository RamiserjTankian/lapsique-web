<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\BookingSlot;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\Event;
use App\Models\TicketOrder;
use App\Models\TicketProduct;
use App\Services\CustomerAnalyticsAttributionService;
use App\Services\TicketOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerAnalyticsAttributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_identifying_a_customer_backfills_existing_analytics_rows_and_future_collects(): void
    {
        config()->set('analytics.ip_lookup.enabled', false);

        $visitorId = (string) Str::uuid();
        $sessionId = (string) Str::uuid();
        $customer = Customer::create([
            'name' => 'Lead Test',
            'email' => 'lead@example.com',
            'status' => 'lead',
        ]);

        $session = AnalyticsSession::create([
            'session_id' => $sessionId,
            'visitor_id' => $visitorId,
            'landing_path' => '/',
            'last_seen_at' => now(),
        ]);

        AnalyticsPageview::create([
            'analytics_session_id' => $session->id,
            'visitor_id' => $visitorId,
            'url' => 'https://lapsique.test/',
            'path' => '/',
        ]);

        AnalyticsEvent::create([
            'analytics_session_id' => $session->id,
            'visitor_id' => $visitorId,
            'name' => 'engaged',
            'path' => '/',
        ]);

        app(CustomerAnalyticsAttributionService::class)->identify($customer, $visitorId, $sessionId, 'test');

        $this->assertDatabaseHas('analytics_visitor_identities', [
            'visitor_id' => $visitorId,
            'customer_id' => $customer->id,
            'source' => 'test',
        ]);
        $this->assertSame($customer->id, $session->fresh()->customer_id);
        $this->assertSame($customer->id, AnalyticsPageview::first()->customer_id);
        $this->assertSame($customer->id, AnalyticsEvent::first()->customer_id);

        $newSessionId = (string) Str::uuid();
        $this->postJson(route('analytics.collect'), [
            'type' => 'pageview',
            'session_id' => $newSessionId,
            'visitor_id' => $visitorId,
            'url' => 'https://lapsique.test/booking',
            'path' => '/booking',
            'title' => 'Booking',
        ])->assertNoContent();

        $this->assertSame(
            $customer->id,
            AnalyticsSession::where('session_id', $newSessionId)->firstOrFail()->customer_id,
        );
    }

    public function test_lead_capture_links_the_visitor_to_the_customer(): void
    {
        Bus::fake();

        $visitorId = (string) Str::uuid();
        $sessionId = (string) Str::uuid();

        AnalyticsSession::create([
            'session_id' => $sessionId,
            'visitor_id' => $visitorId,
            'landing_path' => '/',
            'last_seen_at' => now(),
        ]);

        $this->postJson(route('leads.capture'), [
            'name' => 'Ana Lead',
            'email' => 'ana-lead@example.com',
            'analytics_visitor_id' => $visitorId,
            'analytics_session_id' => $sessionId,
        ])->assertOk();

        $customer = Customer::where('email', 'ana-lead@example.com')->firstOrFail();

        $this->assertDatabaseHas('analytics_visitor_identities', [
            'visitor_id' => $visitorId,
            'customer_id' => $customer->id,
            'source' => 'popup_lead',
        ]);
        $this->assertSame($customer->id, AnalyticsSession::where('session_id', $sessionId)->firstOrFail()->customer_id);
    }

    public function test_guestlist_ticket_and_booking_flows_link_analytics_identity(): void
    {
        Bus::fake();
        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/pay/cs_test_123',
                'status' => 'open',
            ], 200),
        ]);
        config()->set('stripe.secret_key', 'sk_test_fake');
        config()->set('booking.skip_payment_hosts', []);
        config()->set('booking.skip_payment_host_suffixes', []);

        $event = Event::create(['title' => 'Evento Test', 'slug' => 'evento-test']);
        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'Acceso',
            'category' => 'ticket',
            'currency' => 'MXN',
            'price' => 1150,
            'service_charge_pct' => 15,
            'access_units' => 1,
            'check_in_limit' => 1,
            'is_active' => true,
        ]);
        $slot = BookingSlot::create([
            'date' => now()->addDays(3)->toDateString(),
            'time_label' => '2:00 PM',
            'time_value' => '14:00',
            'max_bookings' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        $guestVisitor = (string) Str::uuid();
        $guestSession = (string) Str::uuid();
        AnalyticsSession::create(['session_id' => $guestSession, 'visitor_id' => $guestVisitor, 'landing_path' => '/', 'last_seen_at' => now()]);
        $this->post(route('guestlist.store'), [
            'event_id' => $event->id,
            'full_name' => 'Guest Lead',
            'email' => 'guest@example.com',
            'accepts_emails' => 'on',
            'analytics_visitor_id' => $guestVisitor,
            'analytics_session_id' => $guestSession,
        ]);
        $guestCustomer = Customer::where('email', 'guest@example.com')->firstOrFail();
        $this->assertSame($guestCustomer->id, AnalyticsSession::where('session_id', $guestSession)->firstOrFail()->customer_id);

        $ticketVisitor = (string) Str::uuid();
        $ticketSession = (string) Str::uuid();
        AnalyticsSession::create(['session_id' => $ticketSession, 'visitor_id' => $ticketVisitor, 'landing_path' => '/', 'last_seen_at' => now()]);
        $order = app(TicketOrderService::class)->createOrder(
            $event,
            [$product->id => 1],
            ['name' => 'Ticket Buyer', 'email' => 'ticket@example.com', 'whatsapp' => '9991112233', 'phone' => '9991112233'],
            ['metadata' => ['analytics_visitor_id' => $ticketVisitor, 'analytics_session_id' => $ticketSession]],
        );
        $this->assertInstanceOf(TicketOrder::class, $order);
        $this->assertSame($order->customer_id, AnalyticsSession::where('session_id', $ticketSession)->firstOrFail()->customer_id);

        $bookingVisitor = (string) Str::uuid();
        $bookingSession = (string) Str::uuid();
        AnalyticsSession::create(['session_id' => $bookingSession, 'visitor_id' => $bookingVisitor, 'landing_path' => '/', 'last_seen_at' => now()]);
        $this->post(route('booking.checkout'), [
            'booking_slot_id' => $slot->id,
            'client_name' => 'Booking Buyer',
            'client_email' => 'booking@example.com',
            'client_phone' => '529841234567',
            'terms_accepted' => true,
            'analytics_visitor_id' => $bookingVisitor,
            'analytics_session_id' => $bookingSession,
        ])->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        $booking = ContentBooking::where('client_email', 'booking@example.com')->firstOrFail();
        $this->assertSame($booking->customer_id, AnalyticsSession::where('session_id', $bookingSession)->firstOrFail()->customer_id);
    }
}
