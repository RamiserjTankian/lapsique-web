<?php

namespace Tests\Unit;

use Tests\TestCase;

final class LapsiqueNewsletterConsentContractTest extends TestCase
{
    public function test_newsletter_requires_and_submits_explicit_marketing_and_meta_consent(): void
    {
        $source = (string) file_get_contents(resource_path('js/components/lapsique/NewsletterCaptureModal.tsx'));

        self::assertStringContainsString('if (!marketingConsent)', $source);
        self::assertStringContainsString('marketing_consent: marketingConsent', $source);
        self::assertStringContainsString('meta_marketing_consent: marketingConsent', $source);
        self::assertStringContainsString('name="marketing_consent"', $source);
        self::assertStringContainsString('aria-required="true"', $source);
        self::assertLessThan(
            strpos($source, '<Button type="submit"'),
            strpos($source, 'htmlFor="newsletter-marketing-consent"'),
            'Consent must appear before the submit button in reading and keyboard order.',
        );
        self::assertSame(
            'Acepta el consentimiento de marketing para continuar.',
            trans('funnel.newsletter.consent_required', locale: 'es'),
        );
        self::assertSame(
            'Accept the marketing consent to continue.',
            trans('funnel.newsletter.consent_required', locale: 'en'),
        );
    }
}
