<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BookingAnalyticsClientContractTest extends TestCase
{
    private string $analyticsHook;

    protected function setUp(): void
    {
        parent::setUp();

        $this->analyticsHook = (string) file_get_contents(
            dirname(__DIR__, 2).'/resources/js/hooks/useBookingAnalytics.ts',
        );
    }

    public function test_ga_preserves_explicit_content_format_with_legacy_fallback(): void
    {
        $this->assertStringContainsString(
            'content_format: payload.content_format ?? payload.format',
            $this->analyticsHook,
        );
    }

    public function test_portfolio_browsing_events_remain_custom_meta_events(): void
    {
        preg_match(
            '/const STANDARD_EVENTS: Record<string, string> = \{(?<events>.*?)\};/s',
            $this->analyticsHook,
            $matches,
        );

        $this->assertArrayHasKey('events', $matches);

        foreach ([
            'service_portfolio_viewed',
            'portfolio_project_selected',
            'portfolio_media_played',
            'portfolio_cta_clicked',
        ] as $event) {
            $this->assertStringContainsString("'{$event}'", $this->analyticsHook);
            $this->assertStringNotContainsString($event, $matches['events']);
        }
    }
}
