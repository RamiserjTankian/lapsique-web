<?php

namespace Tests\Feature;

use App\Actions\SyncMetaCampaignInsightsAction;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\Event;
use App\Models\MetaCampaignDailyInsight;
use App\Models\TicketOrder;
use App\Services\Meta\MetaAttributionReportService;
use App\Services\Meta\MetaConversionsApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaAdsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_campaign_insights_upserts_rows(): void
    {
        config([
            'meta-ads.enabled' => true,
            'meta-ads.access_token' => 'test-token',
            'meta-ads.ad_account_id' => 'act_123',
            'meta-ads.api_version' => 'v21.0',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'data' => [
                    [
                        'campaign_id' => '120001',
                        'campaign_name' => 'Sesiones RM',
                        'spend' => '150.50',
                        'impressions' => '1000',
                        'clicks' => '42',
                        'reach' => '800',
                        'cpc' => '3.58',
                        'cpm' => '150.50',
                        'date_start' => now()->toDateString(),
                        'date_stop' => now()->toDateString(),
                    ],
                ],
            ]),
        ]);

        $result = app(SyncMetaCampaignInsightsAction::class)->execute(7);

        $this->assertSame(1, $result['synced']);
        $this->assertDatabaseHas('meta_campaign_daily_insights', [
            'campaign_id' => '120001',
            'campaign_name' => 'Sesiones RM',
        ]);
    }

    public function test_attribution_report_calculates_cpl_and_roas(): void
    {
        $campaignId = '120001';
        $today = now()->toDateString();

        MetaCampaignDailyInsight::create([
            'date' => $today,
            'campaign_id' => $campaignId,
            'campaign_name' => 'Test Campaign',
            'spend' => 200,
            'impressions' => 1000,
            'clicks' => 50,
            'reach' => 900,
            'synced_at' => now(),
        ]);

        Customer::create([
            'name' => 'Lead Popup',
            'email' => 'lead@example.com',
            'source' => 'popup',
            'status' => 'lead',
            'utm_campaign' => $campaignId,
        ]);

        ContentBooking::create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Cliente',
            'client_email' => 'booking@example.com',
            'client_phone' => '9990000000',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
            'utm_campaign' => $campaignId,
        ]);

        $report = app(MetaAttributionReportService::class)->report(
            now()->startOfDay(),
            now()->endOfDay(),
        );

        $row = collect($report['campaigns'])->firstWhere('campaign_id', $campaignId);

        $this->assertNotNull($row);
        $this->assertSame(200.0, (float) $row['spend']);
        $this->assertGreaterThanOrEqual(2, (int) $row['leads']);
        $this->assertSame(1, (int) $row['sales_closed']);
        $this->assertSame(5000.0, (float) $row['revenue']);
        $this->assertSame(100.0, (float) $row['cpl']);
        $this->assertSame(25.0, (float) $row['roas']);
    }

    public function test_conversions_api_sends_purchase_for_booking(): void
    {
        config([
            'meta-ads.capi_enabled' => true,
            'meta-ads.pixel_id' => 'pixel123',
            'meta-ads.access_token' => 'test-token',
            'meta-ads.api_version' => 'v21.0',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $booking = ContentBooking::create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Cliente',
            'client_email' => 'buyer@example.com',
            'client_phone' => '9990000000',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
            'fbp' => 'fb.1.123.456',
        ]);

        app(MetaConversionsApiService::class)->sendPurchaseForBooking($booking);

        Http::assertSent(function ($request) use ($booking) {
            $body = $request->data();

            return str_contains($request->url(), '/pixel123/events')
                && data_get($body, 'data.0.event_name') === 'Purchase'
                && data_get($body, 'data.0.event_id') === 'booking_'.$booking->public_id;
        });
    }

    public function test_ticket_order_observer_sends_purchase_when_paid(): void
    {
        config([
            'meta-ads.capi_enabled' => true,
            'meta-ads.pixel_id' => 'pixel123',
            'meta-ads.access_token' => 'test-token',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $event = Event::create([
            'title' => 'Test Event',
            'slug' => 'test-event-meta',
            'starts_at' => now()->addWeek(),
        ]);

        $order = TicketOrder::create([
            'event_id' => $event->id,
            'status' => 'pending',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 500,
            'total' => 1500,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'buyer_name' => 'Buyer',
            'buyer_email' => 'buyer@example.com',
        ]);

        $order->markAsPaid();

        Http::assertSent(fn ($request) => str_contains($request->url(), '/pixel123/events'));
    }
}
