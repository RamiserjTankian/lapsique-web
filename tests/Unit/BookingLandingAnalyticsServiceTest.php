<?php

namespace Tests\Unit;

use App\Services\BookingLandingAnalyticsService;
use PHPUnit\Framework\TestCase;

class BookingLandingAnalyticsServiceTest extends TestCase
{
    public function test_tracked_event_names_include_reel_and_section_events(): void
    {
        $events = BookingLandingAnalyticsService::trackedEventNames();

        $this->assertContains('reel_card_clicked', $events);
        $this->assertContains('reel_player_opened', $events);
        $this->assertContains('reel_watch_milestone', $events);
        $this->assertContains('reel_overlay_cta_clicked', $events);
        $this->assertContains('reel_player_agendar_clicked', $events);
        $this->assertContains('gear_section_viewed', $events);
        $this->assertContains('workflow_section_viewed', $events);
        $this->assertContains('package_includes_viewed', $events);
        $this->assertContains('header_cta_clicked', $events);
        $this->assertContains('booking_cta_clicked', $events);
        $this->assertContains('video_play', $events);
        $this->assertContains('video_progress', $events);
        $this->assertContains('video_complete', $events);
        $this->assertContains('form_started', $events);
        $this->assertContains('service_landing_lead_form_submitted', $events);
        $this->assertContains('newsletter_form_submitted', $events);
        $this->assertContains('booking_payment_info_added', $events);
        $this->assertContains('content_creation_booking_cta_clicked', $events);
        $this->assertContains('content_creation_whatsapp_cta_clicked', $events);
        $this->assertContains('electronic_event_coverage_portfolio_engaged', $events);
        $this->assertContains('electronic_event_coverage_booking_opened', $events);
        $this->assertContains('electronic_event_coverage_booking_cta_clicked', $events);
        $this->assertContains('electronic_event_coverage_whatsapp_cta_clicked', $events);

        foreach (BookingLandingAnalyticsService::contactEventNames() as $contactEvent) {
            $this->assertContains($contactEvent, $events);
        }
    }

    public function test_stage_definitions_include_reel_funnel_stages(): void
    {
        $stages = collect(BookingLandingAnalyticsService::stageDefinitions())->pluck('key');

        $this->assertTrue($stages->contains('reel_engaged'));
        $this->assertTrue($stages->contains('reel_watch'));
        $this->assertTrue($stages->contains('reel_overlay_cta'));
        $this->assertTrue($stages->contains('reel_modal_cta'));
    }

    public function test_all_public_campaign_landings_are_included_in_reporting_scope(): void
    {
        $this->assertSame([
            '/',
            '/creacion-de-contenido-riviera-maya',
            '/cobertura-eventos-electronica',
            '/reels-de-comida',
            '/dj-set',
            '/sesiones-de-dron',
            '/avances-de-obra',
            '/portafolio',
            '/trabajos-en-video',
        ], BookingLandingAnalyticsService::LANDING_PATHS);
    }
}
