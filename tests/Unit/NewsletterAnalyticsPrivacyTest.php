<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NewsletterAnalyticsPrivacyTest extends TestCase
{
    public function test_newsletter_internal_analytics_strips_pii_before_tracker_payload(): void
    {
        $source = file_get_contents(__DIR__.'/../../resources/js/hooks/useNewsletterAnalytics.ts');

        $this->assertStringContainsString('...internalPayload', $source);
        $this->assertStringContainsString('window.LapsiqueTracker?.track(name, internalPayload)', $source);
        $this->assertStringContainsString('client_email: _clientEmail', $source);
        $this->assertStringContainsString('window.trackMetaPixel?.(\'Lead\'', $source);
    }
}
