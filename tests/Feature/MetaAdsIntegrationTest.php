<?php

namespace Tests\Feature;

use App\Actions\SyncMetaCampaignInsightsAction;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\Event;
use App\Models\MetaCampaignDailyInsight;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketProduct;
use App\Services\Meta\MetaAttributionReportService;
use App\Services\Meta\MetaConversionsApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MetaAdsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function configureMetaCapi(array $overrides = []): void
    {
        config(array_merge([
            'meta.capi.enabled' => true,
            'meta.pixel.id' => 'pixel123',
            'meta.marketing_api.access_token' => 'test-token',
            'meta.marketing_api.api_version' => 'v21.0',
        ], $overrides));
    }

    public function test_sync_campaign_insights_upserts_rows(): void
    {
        config([
            'meta.marketing_api.enabled' => true,
            'meta.marketing_api.access_token' => 'test-token',
            'meta.marketing_api.ad_account_id' => 'act_123',
            'meta.marketing_api.api_version' => 'v21.0',
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
        $reportDay = Carbon::parse('2026-05-20');

        MetaCampaignDailyInsight::create([
            'date' => $reportDay->toDateString(),
            'campaign_id' => $campaignId,
            'campaign_name' => 'Test Campaign',
            'spend' => 200,
            'impressions' => 1000,
            'clicks' => 50,
            'reach' => 900,
            'synced_at' => now(),
        ]);

        $customer = Customer::create([
            'name' => 'Lead Popup',
            'email' => 'lead@example.com',
            'source' => 'popup',
            'status' => 'lead',
            'utm_campaign' => $campaignId,
        ]);
        $customer->forceFill([
            'created_at' => $reportDay,
            'updated_at' => $reportDay,
        ])->save();

        $booking = ContentBooking::create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Cliente',
            'client_email' => 'booking@example.com',
            'client_phone' => '9990000000',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => $reportDay,
            'utm_campaign' => $campaignId,
        ]);
        $booking->forceFill([
            'created_at' => $reportDay,
            'updated_at' => $reportDay,
        ])->save();

        Cache::flush();
        app(MetaAttributionReportService::class)->clearCache();

        $this->assertTrue(Schema::hasTable('meta_campaign_daily_insights'));
        $this->assertSame(1, MetaCampaignDailyInsight::count());

        $report = app(MetaAttributionReportService::class)->report(
            $reportDay->copy()->startOfDay(),
            $reportDay->copy()->endOfDay(),
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
        $this->configureMetaCapi();

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
                && data_get($body, 'data.0.event_id') === 'booking_'.$booking->public_id
                && (float) data_get($body, 'data.0.custom_data.value') === 5000.0
                && data_get($body, 'data.0.custom_data.content_ids') === [(string) $booking->public_id]
                && data_get($body, 'data.0.custom_data.content_name') === $booking->service_name;
        });
    }

    public function test_conversions_api_uses_shared_event_id_for_initiate_checkout(): void
    {
        $this->configureMetaCapi();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $booking = ContentBooking::create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Cliente Checkout',
            'client_email' => 'checkout@example.com',
            'client_phone' => '9990000000',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'metadata' => ['checkout_event_id' => 'browser-evt-123'],
        ]);

        app(MetaConversionsApiService::class)->sendInitiateCheckoutForBooking($booking);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return data_get($body, 'data.0.event_name') === 'InitiateCheckout'
                && data_get($body, 'data.0.event_id') === 'browser-evt-123';
        });
    }

    public function test_purchase_is_only_sent_once_per_booking(): void
    {
        $this->configureMetaCapi();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $booking = ContentBooking::create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Cliente Idempotente',
            'client_email' => 'idempotent@example.com',
            'client_phone' => '9990000000',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
        ]);

        $service = app(MetaConversionsApiService::class);
        $service->sendPurchaseForBooking($booking);
        $service->sendPurchaseForBooking($booking->fresh());

        $this->assertTrue((bool) data_get($booking->fresh()->metadata, 'capi_purchase_sent'));
        Http::assertSentCount(1);
    }

    public function test_failed_capi_purchase_is_not_marked_as_sent_and_can_be_retried(): void
    {
        $this->configureMetaCapi();

        Http::fake([
            'graph.facebook.com/*' => Http::sequence()
                ->push(['error' => ['message' => 'Temporary Meta error']], 500)
                ->push(['events_received' => 1], 200),
        ]);

        $booking = ContentBooking::create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Cliente Retry',
            'client_email' => 'retry@example.com',
            'client_phone' => '9990000000',
            'amount' => 5000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
        ]);

        $service = app(MetaConversionsApiService::class);
        $service->sendPurchaseForBooking($booking);

        $this->assertEmpty(data_get($booking->fresh()->metadata, 'capi_purchase_sent'));

        $service->sendPurchaseForBooking($booking->fresh());

        $this->assertTrue((bool) data_get($booking->fresh()->metadata, 'capi_purchase_sent'));
        Http::assertSentCount(2);
    }

    public function test_successful_http_response_without_received_events_is_not_marked_as_sent(): void
    {
        $this->configureMetaCapi();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 0], 200),
        ]);

        $booking = ContentBooking::create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Cliente Rechazado',
            'client_email' => 'rejected@example.com',
            'client_phone' => '9990000000',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
        ]);

        app(MetaConversionsApiService::class)->sendPurchaseForBooking($booking);

        $this->assertEmpty(data_get($booking->fresh()->metadata, 'capi_purchase_sent'));
    }

    public function test_conversions_api_sends_external_id_for_customer_on_purchase(): void
    {
        $this->configureMetaCapi();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $customer = Customer::create([
            'name' => 'Cliente Meta',
            'email' => 'meta-customer@example.com',
            'status' => 'customer',
        ]);

        $booking = ContentBooking::create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'customer_id' => $customer->id,
            'client_name' => 'Cliente Meta',
            'client_email' => 'meta-customer@example.com',
            'client_phone' => '9990000000',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
        ]);

        app(MetaConversionsApiService::class)->sendPurchaseForBooking($booking);

        $expectedExternalId = hash('sha256', 'customer_'.$customer->id);

        Http::assertSent(function ($request) use ($expectedExternalId) {
            $body = $request->data();

            return str_contains($request->url(), '/pixel123/events')
                && data_get($body, 'data.0.user_data.external_id') === $expectedExternalId;
        });
    }

    public function test_ticket_order_observer_sends_purchase_when_paid(): void
    {
        $this->configureMetaCapi();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $event = Event::create([
            'title' => 'Test Event',
            'slug' => 'test-event-meta',
            'starts_at' => now()->addWeek(),
        ]);
        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'General',
            'price' => 1500,
            'currency' => 'MXN',
            'stock' => 20,
            'is_active' => true,
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
            'metadata' => [
                'fbp' => 'fb.1.123.456',
                'fbc' => 'fb.1.123.click',
            ],
        ]);
        TicketOrderItem::create([
            'ticket_order_id' => $order->id,
            'ticket_product_id' => $product->id,
            'name' => 'General',
            'quantity' => 1,
            'unit_price' => 1500,
            'total_price' => 1500,
            'access_units' => 1,
            'check_in_limit' => 1,
        ]);

        $order->markAsPaid();

        Http::assertSent(function ($request) use ($order, $event, $product) {
            $body = $request->data();

            return str_contains($request->url(), '/pixel123/events')
                && data_get($body, 'data.0.event_name') === 'Purchase'
                && data_get($body, 'data.0.event_id') === 'ticket_order_'.$order->public_id
                && data_get($body, 'data.0.user_data.external_id') === hash('sha256', 'ticket_order_'.$order->public_id)
                && data_get($body, 'data.0.user_data.fbp') === 'fb.1.123.456'
                && data_get($body, 'data.0.custom_data.content_name') === $event->title
                && data_get($body, 'data.0.custom_data.content_ids') === [(string) $product->id]
                && (float) data_get($body, 'data.0.custom_data.value') === 1500.0;
        });
    }

    public function test_ticket_add_payment_info_and_purchase_are_each_deduplicated(): void
    {
        $this->configureMetaCapi();
        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $event = Event::create([
            'title' => 'Safe Meta',
            'slug' => 'safe-meta',
            'starts_at' => now()->addWeek(),
        ]);
        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'General',
            'price' => 105,
            'currency' => 'MXN',
            'stock' => 20,
            'is_active' => true,
        ]);
        $order = TicketOrder::create([
            'event_id' => $event->id,
            'status' => 'pending',
            'payment_provider' => 'mercadopago',
            'currency' => 'MXN',
            'subtotal' => 100,
            'fee' => 5,
            'total' => 105,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'buyer_name' => 'Safe Buyer',
            'buyer_email' => 'safe-meta@example.com',
        ]);
        TicketOrderItem::create([
            'ticket_order_id' => $order->id,
            'ticket_product_id' => $product->id,
            'name' => $product->name,
            'category' => 'ticket',
            'quantity' => 1,
            'unit_price' => 105,
            'total_price' => 105,
            'access_units' => 1,
            'check_in_limit' => 1,
        ]);

        $service = app(MetaConversionsApiService::class);
        $service->sendAddPaymentInfoForTicketOrder($order->fresh(['event', 'items']));
        $service->sendAddPaymentInfoForTicketOrder($order->fresh(['event', 'items']));
        $service->sendPurchaseForTicketOrder($order->fresh(['event', 'items']));
        $service->sendPurchaseForTicketOrder($order->fresh(['event', 'items']));

        $requests = Http::recorded();
        $this->assertCount(2, $requests);
        $this->assertSame('ticket_payment_info_'.$order->public_id, data_get($requests[0][0]->data(), 'data.0.event_id'));
        $this->assertSame('ticket_order_'.$order->public_id, data_get($requests[1][0]->data(), 'data.0.event_id'));
        $this->assertTrue((bool) data_get($order->fresh()->metadata, 'capi_add_payment_info_sent'));
        $this->assertTrue((bool) data_get($order->fresh()->metadata, 'capi_purchase_sent'));
    }

    public function test_conversions_api_sends_initiate_checkout_for_ticket_order_with_browser_event_id(): void
    {
        $this->configureMetaCapi();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $event = Event::create([
            'title' => 'Checkout Event',
            'slug' => 'checkout-event-meta',
            'starts_at' => now()->addWeek(),
        ]);
        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'General',
            'price' => 1200,
            'currency' => 'MXN',
            'stock' => 20,
            'is_active' => true,
        ]);
        $order = TicketOrder::create([
            'event_id' => $event->id,
            'status' => 'pending',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 200,
            'total' => 1200,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'buyer_name' => 'Buyer',
            'buyer_email' => 'buyer@example.com',
            'metadata' => ['checkout_event_id' => 'ticket_checkout_browser_123'],
        ]);
        TicketOrderItem::create([
            'ticket_order_id' => $order->id,
            'ticket_product_id' => $product->id,
            'name' => 'General',
            'quantity' => 1,
            'unit_price' => 1200,
            'total_price' => 1200,
            'access_units' => 1,
            'check_in_limit' => 1,
        ]);

        app(MetaConversionsApiService::class)->sendInitiateCheckoutForTicketOrder($order->fresh(['event', 'items']));

        Http::assertSent(function ($request) use ($event, $product) {
            $body = $request->data();

            return data_get($body, 'data.0.event_name') === 'InitiateCheckout'
                && data_get($body, 'data.0.event_id') === 'ticket_checkout_browser_123'
                && data_get($body, 'data.0.custom_data.content_name') === $event->title
                && data_get($body, 'data.0.custom_data.content_ids') === [(string) $product->id];
        });
    }

    public function test_guestlist_registration_sends_capi_lead_with_guestlist_context(): void
    {
        Bus::fake();
        $this->configureMetaCapi();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $event = Event::create([
            'title' => 'Guestlist Event',
            'slug' => 'guestlist-event-meta',
            'starts_at' => now()->addWeek(),
        ]);

        $this->post(route('guestlist.store'), [
            'event_id' => $event->id,
            'full_name' => 'Guest Lead',
            'email' => 'guest-lead@example.com',
            'whatsapp' => '9991112233',
            'accepts_emails' => 'on',
            'landing_url' => 'https://lapsique.test/eventos/guestlist-event-meta',
        ])->assertSessionHas('success');

        $customer = Customer::where('email', 'guest-lead@example.com')->firstOrFail();

        Http::assertSent(function ($request) use ($event, $customer) {
            $body = $request->data();

            return data_get($body, 'data.0.event_name') === 'Lead'
                && data_get($body, 'data.0.event_id') === 'lead_customer_'.$customer->id
                && data_get($body, 'data.0.custom_data.content_category') === 'guestlist'
                && data_get($body, 'data.0.custom_data.content_name') === $event->title;
        });
    }

    public function test_ticket_success_page_uses_capi_purchase_event_id_for_pixel_deduplication(): void
    {
        $event = Event::create([
            'title' => 'Test Event',
            'slug' => 'test-event-success',
            'starts_at' => now()->addWeek(),
        ]);

        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'General',
            'price' => 1500,
            'currency' => 'MXN',
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = TicketOrder::create([
            'event_id' => $event->id,
            'status' => 'paid',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 500,
            'total' => 1500,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'buyer_name' => 'Buyer',
            'buyer_email' => 'buyer@example.com',
        ]);

        TicketOrderItem::create([
            'ticket_order_id' => $order->id,
            'ticket_product_id' => $product->id,
            'name' => 'General',
            'quantity' => 1,
            'unit_price' => 1500,
            'total_price' => 1500,
            'access_units' => 1,
            'check_in_limit' => 1,
        ]);

        $html = file_get_contents(resource_path('views/tickets/success.blade.php'));

        $this->assertStringContainsString("@json('ticket_order_'.\$order->public_id)", $html);
        $this->assertStringContainsString('eventID: purchaseEventId', $html);
    }
}
