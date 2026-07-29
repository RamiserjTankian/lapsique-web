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
        $this->assertContains('content_creation_page_viewed', $events);
        $this->assertContains('content_creation_booking_cta_clicked', $events);
        $this->assertContains('content_creation_whatsapp_cta_clicked', $events);
        $this->assertContains('business_reels_page_viewed', $events);
        $this->assertContains('business_reels_booking_cta_clicked', $events);
        $this->assertContains('business_reels_whatsapp_cta_clicked', $events);
        $this->assertContains('electronic_event_coverage_portfolio_engaged', $events);
        $this->assertContains('electronic_event_coverage_booking_opened', $events);
        $this->assertContains('electronic_event_coverage_booking_cta_clicked', $events);
        $this->assertContains('electronic_event_coverage_whatsapp_cta_clicked', $events);
        $this->assertContains('multi_camera_portfolio_engaged', $events);
        $this->assertContains('multi_camera_coverage_selected', $events);
        $this->assertContains('multi_camera_format_selected', $events);
        $this->assertContains('multi_camera_video_started', $events);
        $this->assertContains('multi_camera_video_progress', $events);
        $this->assertContains('multi_camera_video_completed', $events);
        $this->assertContains('multi_camera_gear_viewed', $events);
        $this->assertContains('multi_camera_package_viewed', $events);
        $this->assertContains('service_portfolio_viewed', $events);
        $this->assertContains('portfolio_project_selected', $events);
        $this->assertContains('portfolio_media_played', $events);
        $this->assertContains('portfolio_cta_clicked', $events);

        foreach (BookingLandingAnalyticsService::contactEventNames() as $contactEvent) {
            $this->assertContains($contactEvent, $events);
        }
    }

    public function test_portfolio_browsing_events_are_not_classified_as_contact_events(): void
    {
        $contacts = BookingLandingAnalyticsService::contactEventNames();

        $this->assertNotContains('service_portfolio_viewed', $contacts);
        $this->assertNotContains('portfolio_project_selected', $contacts);
        $this->assertNotContains('portfolio_media_played', $contacts);
        $this->assertNotContains('portfolio_cta_clicked', $contacts);
        $this->assertContains('content_creation_whatsapp_cta_clicked', $contacts);
        $this->assertContains('business_reels_whatsapp_cta_clicked', $contacts);
        $this->assertNotContains('content_creation_page_viewed', $contacts);
        $this->assertNotContains('business_reels_page_viewed', $contacts);
        $this->assertNotContains('content_creation_booking_cta_clicked', $contacts);
        $this->assertNotContains('business_reels_booking_cta_clicked', $contacts);
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
            '/reels-para-negocios',
            '/cobertura-eventos-electronica',
            '/reels-de-comida',
            '/dj-set',
            '/sesiones-de-dron',
            '/avances-de-obra',
            '/portafolio',
            '/trabajos-en-video',
            '/multicamara',
        ], BookingLandingAnalyticsService::LANDING_PATHS);
    }
}
