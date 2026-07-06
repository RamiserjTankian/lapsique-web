<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MetaPixelClientTest extends TestCase
{
    public function test_pixel_client_supports_inertia_and_blade_queues_with_event_ids(): void
    {
        $pixelSource = file_get_contents(__DIR__.'/../../resources/js/pixel.js');
        $analyticsHook = file_get_contents(__DIR__.'/../../resources/js/hooks/useBookingAnalytics.ts');
        $inertiaApp = file_get_contents(__DIR__.'/../../resources/js/app.tsx');
        $whatsappFab = file_get_contents(__DIR__.'/../../resources/js/components/lapsique/WhatsAppFab.tsx');
        $inertiaLayout = file_get_contents(__DIR__.'/../../resources/views/app.blade.php');
        $bladeLayout = file_get_contents(__DIR__.'/../../resources/views/layouts/site.blade.php');

        $this->assertStringContainsString('window.SitePixel || window.LapsiquePixel', $pixelSource);
        $this->assertStringContainsString('window.__sitePixelQueue', $pixelSource);
        $this->assertStringContainsString('window.__lapsiquePixelQueue', $pixelSource);
        $this->assertStringContainsString('queuedCall.options', $pixelSource);

        $this->assertStringContainsString('function (eventName, payload, options)', $inertiaLayout);
        $this->assertStringContainsString('options: options || null', $inertiaLayout);
        $this->assertStringContainsString('function (eventName, payload, options)', $bladeLayout);
        $this->assertStringContainsString('options: options || null', $bladeLayout);

        $this->assertStringContainsString("food_reels_page_viewed: 'ViewContent'", $analyticsHook);
        $this->assertStringContainsString("food_reels_booking_cta_clicked: 'Lead'", $analyticsHook);
        $this->assertStringContainsString("food_reels_whatsapp_cta_clicked: 'Contact'", $analyticsHook);
        $this->assertStringContainsString("djset_page_viewed: 'ViewContent'", $analyticsHook);
        $this->assertStringContainsString("djset_booking_cta_clicked: 'Lead'", $analyticsHook);
        $this->assertStringContainsString("djset_whatsapp_cta_clicked: 'Contact'", $analyticsHook);
        $this->assertStringContainsString("drone_session_page_viewed: 'ViewContent'", $analyticsHook);
        $this->assertStringContainsString("construction_progress_page_viewed: 'ViewContent'", $analyticsHook);
        $this->assertStringContainsString("'construction-progress.show' => 'construction_progress'", $inertiaLayout);
        $this->assertStringContainsString("'food-reels.show' => 'food_reels'", $inertiaLayout);
        $this->assertStringContainsString("'drone-sessions.show' => 'drone_session'", $inertiaLayout);
        $this->assertStringContainsString("'djset.show' => 'dj_set'", $inertiaLayout);
        $this->assertStringContainsString('window.SitePixel?.trackPageView !== false', $inertiaApp);
        $this->assertStringContainsString("trackBookingEvent('whatsapp_popup_clicked'", $whatsappFab);
        $this->assertStringContainsString("'food_reels'", $whatsappFab);
        $this->assertStringContainsString('prefill_food_reels', $whatsappFab);
        $this->assertStringContainsString('$metaPixelTrackPageView', $inertiaLayout);
        $this->assertStringContainsString('$metaPixelTrackPageView', $bladeLayout);
    }
}
