<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Event;
use App\Models\TicketOrder;
use App\Support\EventSalesInsights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventSalesInsightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_event_funnel_and_entry_breakdown(): void
    {
        $event = Event::create([
            'title' => 'Analytics Test Event',
            'slug' => 'analytics-test-event',
        ]);

        $eventPath = parse_url(route('events.show', $event, false), PHP_URL_PATH);

        $visitorOne = (string) Str::uuid();
        $visitorTwo = (string) Str::uuid();
        $sessionOneId = (string) Str::uuid();
        $sessionTwoId = (string) Str::uuid();

        $sessionOne = AnalyticsSession::create([
            'session_id' => $sessionOneId,
            'visitor_id' => $visitorOne,
            'utm_source' => 'instagram',
            'utm_medium' => 'paid-social',
            'device_type' => 'mobile',
            'referrer_domain' => 'instagram.com',
            'landing_path' => $eventPath,
            'last_seen_at' => now(),
        ]);

        $sessionTwo = AnalyticsSession::create([
            'session_id' => $sessionTwoId,
            'visitor_id' => $visitorTwo,
            'device_type' => 'desktop',
            'landing_path' => $eventPath,
            'last_seen_at' => now(),
        ]);

        AnalyticsPageview::create([
            'analytics_session_id' => $sessionOne->id,
            'visitor_id' => $visitorOne,
            'url' => 'https://example.test' . $eventPath,
            'path' => $eventPath,
        ]);

        AnalyticsPageview::create([
            'analytics_session_id' => $sessionTwo->id,
            'visitor_id' => $visitorTwo,
            'url' => 'https://example.test' . $eventPath,
            'path' => $eventPath,
        ]);

        foreach ([
            ['visitor_id' => $visitorOne, 'analytics_session_id' => $sessionOne->id, 'name' => 'section_view', 'label' => 'tickets'],
            ['visitor_id' => $visitorTwo, 'analytics_session_id' => $sessionTwo->id, 'name' => 'section_view', 'label' => 'tickets'],
            ['visitor_id' => $visitorOne, 'analytics_session_id' => $sessionOne->id, 'name' => 'tickets_added_to_cart', 'label' => 'vip'],
            ['visitor_id' => $visitorOne, 'analytics_session_id' => $sessionOne->id, 'name' => 'checkout_started', 'label' => 'vip'],
            ['visitor_id' => $visitorOne, 'analytics_session_id' => $sessionOne->id, 'name' => 'checkout_submitted', 'label' => 'vip'],
        ] as $payload) {
            AnalyticsEvent::create([
                'analytics_session_id' => $payload['analytics_session_id'],
                'visitor_id' => $payload['visitor_id'],
                'name' => $payload['name'],
                'label' => $payload['label'],
                'path' => $eventPath,
                'metadata' => [
                    'event_id' => (string) $event->id,
                ],
            ]);
        }

        $aggregateRecord = TicketOrder::create([
            'event_id' => $event->id,
            'status' => 'paid',
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer1@example.com',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 150,
            'total' => 1150,
            'attendees_expected' => 2,
            'attendees_registered' => 1,
            'paid_at' => now(),
            'metadata' => [
                'analytics_visitor_id' => $visitorOne,
                'analytics_session_id' => $sessionOneId,
            ],
        ]);

        $aggregateRecord->forceFill([
            'orders_count' => 1,
            'tickets_sold' => 2,
            'tickets_registered' => 1,
            'revenue_subtotal' => 1000,
            'revenue_fee' => 150,
            'revenue_total' => 1150,
        ]);
        $aggregateRecord->setRelation('event', $event);

        $insights = new EventSalesInsights($aggregateRecord);
        $summary = $insights->summary();

        $this->assertSame(2, $summary['entry_visitors']);
        $this->assertSame(2, $summary['ticket_visitors']);
        $this->assertSame(1, $summary['cart_visitors']);
        $this->assertSame(1, $summary['checkout_started_visitors']);
        $this->assertSame(1, $summary['checkout_submitted_visitors']);
        $this->assertSame(1, $summary['paid_visitors']);
        $this->assertSame(1, $summary['paid_customers']);
        $this->assertSame(50.0, $summary['visitor_to_paid_rate']);

        $sourceRow = $insights->sourceBreakdown()->firstWhere('source', 'instagram / paid-social');

        $this->assertNotNull($sourceRow);
        $this->assertSame(1, $sourceRow['visitors']);
        $this->assertSame(1, $sourceRow['paid']);
        $this->assertSame(100.0, $sourceRow['conversion']);

        $this->assertCount(6, $insights->funnelRows());
        $this->assertSame(2, $insights->deviceBreakdown()->sum('visitors'));
        $this->assertSame(1, array_sum($insights->timeline()['paid']));
    }
}
