<?php

namespace Tests\Unit;

use App\Models\ContentBooking;
use App\Services\Meta\MetaConversionsApiService;
use App\Support\Meta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_pixel_enabled_requires_config_and_id(): void
    {
        config([
            'meta.pixel.enabled' => true,
            'meta.pixel.id' => '12345',
        ]);

        $this->assertSame('12345', Meta::pixelId());
        $this->assertTrue(Meta::pixelEnabled());

        config(['meta.pixel.enabled' => false]);
        $this->assertFalse(Meta::pixelEnabled());
    }

    public function test_capi_skips_test_booking_purchase(): void
    {
        config([
            'meta.capi.enabled' => true,
            'meta.pixel.id' => '123456789',
            'meta.marketing_api.access_token' => 'test-token',
        ]);

        Http::fake();

        $booking = new ContentBooking([
            'public_id' => 'test-uuid',
            'amount' => 5000,
            'currency' => 'MXN',
            'metadata' => ['skip_payment_mode' => true],
        ]);

        app(MetaConversionsApiService::class)->sendPurchaseForBooking($booking);

        Http::assertNothingSent();
    }

    public function test_capi_sends_purchase_with_stable_event_id(): void
    {
        config([
            'meta.capi.enabled' => true,
            'meta.pixel.id' => '123456789',
            'meta.marketing_api.access_token' => 'test-token',
            'meta.marketing_api.api_version' => 'v21.0',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        $booking = new ContentBooking([
            'public_id' => 'paid-uuid',
            'client_name' => 'Buyer',
            'client_email' => 'buyer@example.com',
            'client_phone' => '529841234567',
            'amount' => 5000,
            'currency' => 'MXN',
            'metadata' => [],
        ]);

        app(MetaConversionsApiService::class)->sendPurchaseForBooking($booking);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return str_contains($request->url(), '123456789/events')
                && ($body['data'][0]['event_name'] ?? null) === 'Purchase'
                && ($body['data'][0]['event_id'] ?? null) === 'booking_paid-uuid';
        });
    }
}
