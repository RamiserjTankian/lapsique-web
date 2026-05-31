<?php

namespace Tests\Unit;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\CustomerEventBalance;
use App\Models\Event;
use App\Models\GuestListEntry;
use App\Models\PosCharge;
use App\Models\TicketOrder;
use App\Services\CustomerJourneyInsightsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerJourneyInsightsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_snapshot_combines_traffic_leads_sales_and_pos(): void
    {
        $customer = Customer::create([
            'name' => 'Journey Customer',
            'email' => 'journey@example.com',
            'status' => 'customer',
            'lead_score' => 80,
            'last_interaction_at' => now(),
        ]);
        $event = Event::create([
            'title' => 'Journey Event',
            'slug' => 'journey-event',
        ]);

        $session = AnalyticsSession::create([
            'session_id' => (string) Str::uuid(),
            'visitor_id' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'source_type' => 'campaign',
            'source_label' => 'instagram',
            'utm_source' => 'instagram',
            'landing_path' => '/',
            'last_seen_at' => now(),
        ]);

        AnalyticsEvent::create([
            'analytics_session_id' => $session->id,
            'visitor_id' => $session->visitor_id,
            'customer_id' => $customer->id,
            'name' => 'engaged',
            'path' => '/',
        ]);

        TicketOrder::create([
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'paid',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 150,
            'total' => 1150,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'attendees_registered' => 1,
            'buyer_name' => 'Journey Customer',
            'buyer_email' => 'journey@example.com',
            'paid_at' => now(),
        ]);

        ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'client_name' => 'Journey Customer',
            'client_email' => 'journey@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
        ]);

        GuestListEntry::create([
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'confirmed',
        ]);

        $balance = CustomerEventBalance::create([
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'currency' => 'MXN',
            'balance' => 300,
            'total_credited' => 500,
            'total_consumed' => 200,
        ]);

        PosCharge::create([
            'customer_event_balance_id' => $balance->id,
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'item_key' => 'agua_mineral',
            'item_name' => 'Agua mineral',
            'item_type' => 'beverage',
            'currency' => 'MXN',
            'quantity' => 2,
            'unit_price' => 100,
            'total' => 200,
            'balance_before' => 500,
            'balance_after' => 300,
        ]);

        $snapshot = app(CustomerJourneyInsightsService::class)->dashboard(30);

        $this->assertSame(1, $snapshot['stats']['visitors']);
        $this->assertSame(1, $snapshot['stats']['engaged_sessions']);
        $this->assertSame(1, $snapshot['stats']['identified_leads']);
        $this->assertSame(1, $snapshot['stats']['paid_customers']);
        $this->assertSame(1, $snapshot['stats']['repeat_customers']);
        $this->assertSame(1150.0, $snapshot['stats']['ticket_revenue']);
        $this->assertSame(3000.0, $snapshot['stats']['booking_revenue']);
        $this->assertSame(200.0, $snapshot['stats']['pos_consumed']);
        $this->assertSame(100.0, $snapshot['funnel'][1]['conversion_rate']);
        $this->assertSame('instagram', $snapshot['sources'][0]['source']);
        $this->assertSame(4150.0, $snapshot['sources'][0]['revenue']);
    }

    public function test_dashboard_does_not_duplicate_revenue_across_multiple_sources_for_one_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Multi Source',
            'email' => 'multi-source@example.com',
            'status' => 'customer',
            'last_interaction_at' => now(),
        ]);
        $event = Event::create(['title' => 'Revenue Event', 'slug' => 'revenue-event']);

        AnalyticsSession::create([
            'session_id' => (string) Str::uuid(),
            'visitor_id' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'source_label' => 'instagram',
            'source_type' => 'campaign',
            'landing_path' => '/',
            'last_seen_at' => now(),
            'created_at' => now()->subDay(),
        ]);
        AnalyticsSession::create([
            'session_id' => (string) Str::uuid(),
            'visitor_id' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'source_label' => 'google',
            'source_type' => 'search',
            'landing_path' => '/',
            'last_seen_at' => now(),
            'created_at' => now(),
        ]);

        TicketOrder::create([
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'paid',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 0,
            'total' => 1000,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'attendees_registered' => 1,
            'buyer_name' => 'Multi Source',
            'buyer_email' => 'multi-source@example.com',
            'paid_at' => now(),
        ]);

        $snapshot = app(CustomerJourneyInsightsService::class)->dashboard(30);

        $this->assertSame(1000.0, collect($snapshot['sources'])->sum('revenue'));
        $this->assertSame(0, $snapshot['stats']['repeat_customers']);
    }
}
