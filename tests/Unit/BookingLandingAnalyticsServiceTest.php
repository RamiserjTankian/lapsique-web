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
    }

    public function test_stage_definitions_include_reel_funnel_stages(): void
    {
        $stages = collect(BookingLandingAnalyticsService::stageDefinitions())->pluck('key');

        $this->assertTrue($stages->contains('reel_engaged'));
        $this->assertTrue($stages->contains('reel_watch'));
        $this->assertTrue($stages->contains('reel_overlay_cta'));
        $this->assertTrue($stages->contains('reel_modal_cta'));
    }
}
