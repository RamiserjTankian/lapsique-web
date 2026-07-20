<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\ContentBooking;
use App\Services\BookingLandingAnalyticsService;
use App\Services\Meta\MetaConversionsApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ElectronicEventCoverageAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_electronic_event_coverage_funnel_is_reported_with_contact_and_shared_checkout_events(): void
    {
        $session = AnalyticsSession::create([
            'session_id' => (string) Str::uuid(),
            'visitor_id' => (string) Str::uuid(),
            'landing_url' => 'https://lapsique.media/cobertura-eventos-electronica',
            'landing_path' => '/cobertura-eventos-electronica',
            'last_seen_at' => now(),
        ]);

        foreach ([
            'electronic_event_coverage_page_viewed',
            'electronic_event_coverage_portfolio_engaged',
            'electronic_event_coverage_booking_cta_clicked',
            'electronic_event_coverage_booking_opened',
            'electronic_event_coverage_whatsapp_cta_clicked',
            'booking_checkout_started',
            'booking_confirmed',
        ] as $name) {
            AnalyticsEvent::create([
                'analytics_session_id' => $session->id,
                'visitor_id' => $session->visitor_id,
                'name' => $name,
                'category' => 'booking_funnel',
                'url' => $session->landing_url,
                'path' => $session->landing_path,
            ]);
        }

        $analytics = app(BookingLandingAnalyticsService::class);
        $reportedSession = $analytics->baseQuery()->sole();
        $snapshot = $analytics->snapshotForQuery($analytics->baseQuery());

        $this->assertSame(7, $reportedSession->booking_events_count);
        $this->assertSame(1, $reportedSession->contact_events_count);
        $this->assertSame(1, $snapshot['stats']['sessions']);
        $this->assertSame(1, $snapshot['stats']['contact']);
        $this->assertSame(1, $snapshot['stats']['confirmed']);
    }

    public function test_landing_has_the_analytics_contract_without_lead_or_schedule_cta_mappings(): void
    {
        $analyticsHook = file_get_contents(resource_path('js/hooks/useBookingAnalytics.ts'));
        $landing = file_get_contents(resource_path('js/pages/EventCoverage/Show.tsx'));
        $confirmation = file_get_contents(resource_path('js/pages/Booking/Confirm.tsx'));
        $bookingWidget = file_get_contents(resource_path('js/components/lapsique/BookingWidget.tsx'));

        $this->get(route('electronic-event-coverage.show'))
            ->assertOk()
            ->assertSee('"serviceType":"electronic_event_coverage"', false);

        $this->assertStringContainsString("trackBookingEvent('electronic_event_coverage_page_viewed'", $landing);
        $this->assertStringContainsString("'electronic_event_coverage_portfolio_engaged'", $landing);
        $this->assertStringContainsString("section: 'event_coverage_portfolio'", $landing);
        $this->assertStringContainsString("analyticsEvent: 'electronic_event_coverage_booking_cta_clicked'", $landing);
        $this->assertStringContainsString('analyticsOpenEvent="electronic_event_coverage_booking_opened"', $landing);
        $this->assertStringContainsString('analyticsOpenEvent?: string', $bookingWidget);
        $this->assertStringContainsString('trackBookingEvent(analyticsOpenEvent, openPayload)', $bookingWidget);
        $this->assertStringContainsString("trackBookingEvent('electronic_event_coverage_whatsapp_cta_clicked'", $landing);
        $this->assertStringContainsString("electronic_event_coverage_page_viewed: 'ViewContent'", $analyticsHook);
        $this->assertStringContainsString("electronic_event_coverage_whatsapp_cta_clicked: 'Contact'", $analyticsHook);
        $this->assertStringNotContainsString("electronic_event_coverage_booking_cta_clicked: 'Lead'", $analyticsHook);
        $this->assertStringNotContainsString("electronic_event_coverage_booking_cta_clicked: 'Schedule'", $analyticsHook);
        $this->assertStringContainsString('if (paymentVerified)', $confirmation);
        $this->assertStringContainsString("trackBookingEvent('booking_confirmed'", $confirmation);
        $this->assertStringContainsString('service_type: booking.service_type', $confirmation);
        $this->assertStringContainsString("return 'electronic_event_coverage_booking'", $confirmation);
    }

    public function test_verified_purchase_uses_the_event_coverage_category_in_shared_meta_capi_flow(): void
    {
        config([
            'meta.capi.enabled' => true,
            'meta.pixel.id' => 'pixel123',
            'meta.marketing_api.access_token' => 'test-token',
            'meta.marketing_api.api_version' => 'v21.0',
        ]);
        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'service_type' => 'electronic_event_coverage',
            'client_name' => 'Cliente Evento',
            'client_email' => 'evento@example.com',
            'client_phone' => '9990000000',
            'amount' => 4500,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
            'landing_url' => 'https://lapsique.media/cobertura-eventos-electronica',
        ]);

        app(MetaConversionsApiService::class)->sendPurchaseForBooking($booking);

        Http::assertSent(function ($request) use ($booking): bool {
            return data_get($request->data(), 'data.0.event_name') === 'Purchase'
                && data_get($request->data(), 'data.0.event_id') === 'booking_'.$booking->public_id
                && data_get($request->data(), 'data.0.event_source_url') === $booking->landing_url
                && data_get($request->data(), 'data.0.custom_data.content_category') === 'electronic_event_coverage_booking';
        });
    }
}
