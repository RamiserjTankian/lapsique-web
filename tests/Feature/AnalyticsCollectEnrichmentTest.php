<?php

namespace Tests\Feature;

use App\Models\AnalyticsSession;
use App\Services\AnalyticsInsightsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsCollectEnrichmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_collect_enriches_session_and_feeds_dashboard_snapshot(): void
    {
        config()->set('analytics.ip_lookup.enabled', false);

        $payload = [
            'type' => 'pageview',
            'session_id' => (string) Str::uuid(),
            'visitor_id' => (string) Str::uuid(),
            'url' => 'https://example.test/evento/zal-marina?utm_source=instagram&utm_medium=paid-social',
            'path' => '/evento/zal-marina',
            'title' => 'Zal Marina',
            'country_name' => 'Mexico',
            'region' => 'Yucatan',
            'region_code' => 'YUC',
            'city' => 'Merida',
        ];

        $this->withHeaders([
            'CF-IPCountry' => 'MX',
        ])->postJson(route('analytics.collect'), $payload)->assertNoContent();

        $session = AnalyticsSession::query()->firstOrFail();

        $this->assertSame('social', $session->source_type);
        $this->assertSame('instagram', $session->source_label);
        $this->assertSame('MX', $session->country);
        $this->assertSame('Mexico', $session->country_name);
        $this->assertSame('Yucatan', $session->region);
        $this->assertSame('YUC', $session->region_code);
        $this->assertSame('Merida', $session->city);

        $snapshot = app(AnalyticsInsightsService::class)->dashboard(30);

        $this->assertSame(1, $snapshot['stats']['sessions']);
        $this->assertSame(1, $snapshot['stats']['pageviews']);
        $this->assertSame('Social', $snapshot['source_breakdown'][0]['label']);
        $this->assertSame('instagram', $snapshot['source_breakdown'][0]['top_source']);
    }
}
