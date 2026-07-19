<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Services\BookingLandingAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingLandingAnalyticsContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_counts_explicit_and_raw_whatsapp_contacts_and_successful_leads(): void
    {
        $explicitSession = $this->createAnalyticsSession('/reels-de-comida');
        $rawLinkSession = $this->createAnalyticsSession('/dj-set');

        $this->createAnalyticsEvent($explicitSession, 'food_reels_whatsapp_cta_clicked');
        $this->createAnalyticsEvent($explicitSession, 'service_landing_lead_form_submitted');
        $this->createAnalyticsEvent($rawLinkSession, 'click', 'https://api.whatsapp.com/send?phone=5219841520127');

        $service = app(BookingLandingAnalyticsService::class);
        $snapshot = $service->snapshotForQuery($service->baseQuery());

        $this->assertSame(2, $snapshot['stats']['contact']);
        $this->assertSame(1, $snapshot['stats']['lead_submitted']);
    }

    protected function createAnalyticsSession(string $path): AnalyticsSession
    {
        return AnalyticsSession::create([
            'session_id' => (string) Str::uuid(),
            'visitor_id' => (string) Str::uuid(),
            'landing_url' => 'https://lapsique.media'.$path,
            'landing_path' => $path,
            'last_seen_at' => now(),
        ]);
    }

    protected function createAnalyticsEvent(AnalyticsSession $session, string $name, ?string $href = null): AnalyticsEvent
    {
        return AnalyticsEvent::create([
            'analytics_session_id' => $session->id,
            'visitor_id' => $session->visitor_id,
            'name' => $name,
            'category' => 'booking_funnel',
            'url' => $session->landing_url,
            'path' => $session->landing_path,
            'element_href' => $href,
        ]);
    }
}
