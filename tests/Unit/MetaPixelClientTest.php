<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MetaPixelClientTest extends TestCase
{
    public function test_pixel_client_supports_inertia_and_blade_queues_with_event_ids(): void
    {
        $pixelSource = file_get_contents(__DIR__.'/../../resources/js/pixel.js');
        $analyticsHook = file_get_contents(__DIR__.'/../../resources/js/hooks/useBookingAnalytics.ts');
        $newsletterHook = file_get_contents(__DIR__.'/../../resources/js/hooks/useNewsletterAnalytics.ts');
        $analyticsSource = file_get_contents(__DIR__.'/../../resources/js/analytics.js');
        $homePage = file_get_contents(__DIR__.'/../../resources/js/pages/Home.tsx');
        $inertiaApp = file_get_contents(__DIR__.'/../../resources/js/app.tsx');
        $whatsappFab = file_get_contents(__DIR__.'/../../resources/js/components/lapsique/WhatsAppFab.tsx');
        $inertiaLayout = file_get_contents(__DIR__.'/../../resources/views/app.blade.php');
        $bladeLayout = file_get_contents(__DIR__.'/../../resources/views/layouts/site.blade.php');

        $this->assertStringContainsString('window.SitePixel || window.LapsiquePixel', $pixelSource);
        $this->assertStringContainsString('window.__sitePixelQueue', $pixelSource);
        $this->assertStringContainsString('window.__lapsiquePixelQueue', $pixelSource);
        $this->assertStringContainsString('queuedCall.options', $pixelSource);
        $this->assertStringContainsString("withPrefix('customer_', identifiers.customer_id)", $pixelSource);
        $this->assertStringContainsString("withPrefix('booking_', identifiers.booking_id)", $pixelSource);
        $this->assertStringContainsString('/^lead_customer_(.+)$/', $pixelSource);
        $this->assertStringContainsString("window.trackMetaPixelCustom('WhatsAppClick'", $pixelSource);
        $this->assertStringContainsString("window.trackMetaPixelCustom('LeadCtaClick'", $pixelSource);
        $this->assertStringNotContainsString('data-meta-event="Lead"', $homePage);

        $this->assertStringContainsString('function (eventName, payload, options)', $inertiaLayout);
        $this->assertStringContainsString('options: options || null', $inertiaLayout);
        $this->assertStringContainsString('function (eventName, payload, options)', $bladeLayout);
        $this->assertStringContainsString('options: options || null', $bladeLayout);

        $this->assertStringContainsString("food_reels_page_viewed: 'ViewContent'", $analyticsHook);
        $this->assertStringContainsString("food_reels_whatsapp_cta_clicked: 'Contact'", $analyticsHook);
        $this->assertStringContainsString("djset_page_viewed: 'ViewContent'", $analyticsHook);
        $this->assertStringContainsString("djset_whatsapp_cta_clicked: 'Contact'", $analyticsHook);
        $this->assertStringContainsString("drone_session_page_viewed: 'ViewContent'", $analyticsHook);
        $this->assertStringContainsString("construction_progress_page_viewed: 'ViewContent'", $analyticsHook);
        $this->assertStringNotContainsString("food_reels_booking_cta_clicked: 'Lead'", $analyticsHook);
        $this->assertStringNotContainsString("djset_booking_cta_clicked: 'Lead'", $analyticsHook);
        $this->assertStringNotContainsString("hero_cta_clicked: 'Lead'", $analyticsHook);
        $this->assertStringNotContainsString("booking_form_started: 'Lead'", $analyticsHook);
        $this->assertStringNotContainsString("booking_date_selected: 'Schedule'", $analyticsHook);
        $this->assertStringContainsString("service_landing_lead_form_submitted: 'Lead'", $analyticsHook);
        $this->assertStringContainsString("booking_confirmed: 'Purchase'", $analyticsHook);
        $this->assertStringContainsString("booking_confirmed: 'BookingConfirmed'", $analyticsHook);
        $this->assertStringContainsString("booking_form_submitted: 'BookingFormSubmitted'", $analyticsHook);
        $this->assertStringContainsString("whatsapp_popup_clicked: 'WhatsAppClick'", $analyticsHook);
        $this->assertStringContainsString("window.trackMetaPixelCustom?.('LeadSubmitted'", $newsletterHook);
        $this->assertStringContainsString("window.trackMetaPixelCustom?.('NewsletterSignup'", $newsletterHook);
        $this->assertStringContainsString('window.SiteAnalytics || window.LapsiqueAnalytics', $analyticsSource);
        $this->assertStringContainsString('window.__lapsiqueTrackerQueue', $analyticsSource);
        $this->assertStringContainsString('window.LapsiqueTracker = trackerApi', $analyticsSource);
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
