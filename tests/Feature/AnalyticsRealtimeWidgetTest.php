<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Services\AnalyticsRealtimeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsRealtimeWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_heartbeat_updates_presence_and_snapshot_groups_active_and_recent_exits(): void
    {
        Carbon::setTestNow('2026-03-19 20:00:00');

        config()->set('analytics.ip_lookup.enabled', false);
        config()->set('analytics.presence.active_window_seconds', 45);
        config()->set('analytics.presence.recent_window_minutes', 15);

        $activeSessionId = (string) Str::uuid();
        $visitorId = (string) Str::uuid();

        $this->postJson(route('analytics.collect'), [
            'type' => 'pageview',
            'session_id' => $activeSessionId,
            'visitor_id' => $visitorId,
            'url' => 'https://example.test/',
            'path' => '/',
            'title' => 'Inicio',
        ])->assertNoContent();

        Carbon::setTestNow('2026-03-19 20:00:20');

        $this->postJson(route('analytics.collect'), [
            'type' => 'heartbeat',
            'session_id' => $activeSessionId,
            'visitor_id' => $visitorId,
            'url' => 'https://example.test/',
            'path' => '/',
            'title' => 'Inicio',
        ])->assertNoContent();

        $inactiveSession = AnalyticsSession::query()->create([
            'session_id' => (string) Str::uuid(),
            'visitor_id' => (string) Str::uuid(),
            'landing_path' => '/tickets',
            'landing_url' => 'https://example.test/tickets',
            'device_type' => 'mobile',
            'browser' => 'safari',
            'last_seen_at' => Carbon::now()->subMinutes(2),
            'created_at' => Carbon::now()->subMinutes(6),
            'updated_at' => Carbon::now()->subMinutes(2),
        ]);

        AnalyticsPageview::query()->create([
            'analytics_session_id' => $inactiveSession->id,
            'visitor_id' => $inactiveSession->visitor_id,
            'url' => 'https://example.test/tickets',
            'path' => '/tickets',
            'title' => 'Tickets',
            'created_at' => Carbon::now()->subMinutes(6),
            'updated_at' => Carbon::now()->subMinutes(6),
        ]);

        AnalyticsEvent::query()->create([
            'analytics_session_id' => $inactiveSession->id,
            'analytics_pageview_id' => null,
            'visitor_id' => $inactiveSession->visitor_id,
            'name' => 'page_exit',
            'category' => 'engagement',
            'path' => '/tickets',
            'value' => 240,
            'created_at' => Carbon::now()->subMinutes(2),
            'updated_at' => Carbon::now()->subMinutes(2),
        ]);

        $freshSession = AnalyticsSession::query()->where('session_id', $activeSessionId)->firstOrFail();

        $this->assertNotNull($freshSession->last_seen_at);
        $this->assertSame('2026-03-19 20:00:20', $freshSession->last_seen_at?->format('Y-m-d H:i:s'));

        $snapshot = app(AnalyticsRealtimeService::class)->snapshot();

        $this->assertSame(1, $snapshot['stats']['active_sessions']);
        $this->assertSame(1, $snapshot['stats']['active_visitors']);
        $this->assertSame(2, $snapshot['stats']['recent_entries']);
        $this->assertSame(1, $snapshot['stats']['recent_exits']);
        $this->assertSame('/', $snapshot['active_sessions_list'][0]['current_path']);
        $this->assertSame('/tickets', $snapshot['recent_exits_list'][0]['current_path']);
        $this->assertGreaterThan(0, $snapshot['stats']['avg_duration_seconds']);

        Carbon::setTestNow();
    }
}
