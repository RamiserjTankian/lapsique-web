<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Services\BookingLandingAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContentCreationAnalyticsRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_creation_ctas_are_included_in_landing_reporting(): void
    {
        $session = AnalyticsSession::create([
            'session_id' => (string) Str::uuid(),
            'visitor_id' => (string) Str::uuid(),
            'landing_url' => 'https://lapsique.media/creacion-de-contenido-riviera-maya',
            'landing_path' => '/creacion-de-contenido-riviera-maya',
            'last_seen_at' => now(),
        ]);

        foreach ([
            'content_creation_booking_cta_clicked',
            'content_creation_whatsapp_cta_clicked',
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

        $this->assertSame(2, $reportedSession->booking_events_count);
        $this->assertSame(1, $reportedSession->contact_events_count);
        $this->assertEqualsCanonicalizing([
            'content_creation_booking_cta_clicked',
            'content_creation_whatsapp_cta_clicked',
        ], $reportedSession->events->pluck('name')->all());
        $this->assertSame(1, $snapshot['stats']['sessions']);
        $this->assertSame(1, $snapshot['stats']['contact']);
    }

    public function test_content_creation_ctas_have_explicit_meta_and_ga_mappings(): void
    {
        $analyticsHook = file_get_contents(resource_path('js/hooks/useBookingAnalytics.ts'));

        $this->assertStringContainsString("content_creation_whatsapp_cta_clicked: 'Contact'", $analyticsHook);
        $this->assertStringContainsString("content_creation_booking_cta_clicked: 'booking_cta_click'", $analyticsHook);
        $this->assertStringContainsString("content_creation_whatsapp_cta_clicked: 'whatsapp_click'", $analyticsHook);
        $this->assertStringContainsString("content_creation_booking_cta_clicked: 'BookingCtaClick'", $analyticsHook);
        $this->assertStringContainsString("content_creation_whatsapp_cta_clicked: 'WhatsAppClick'", $analyticsHook);
        $this->assertStringNotContainsString("content_creation_booking_cta_clicked: 'Lead'", $analyticsHook);
    }

    public function test_content_creation_route_exposes_content_session_service_context(): void
    {
        $this->get(route('content-creation.show'))
            ->assertOk()
            ->assertSee('"serviceType":"content_session"', false);
    }
}
