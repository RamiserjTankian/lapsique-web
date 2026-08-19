<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketOrder;
use App\Models\TicketProduct;
use App\Services\TicketOrderService;
use App\Services\TicketPassPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MercadoPagoEmbeddedPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://lapsique.test',
            'mercadopago.api_base_url' => 'https://api.mercadopago.com',
            'mercadopago.access_token' => 'TEST-access-token',
            'mercadopago.public_key' => 'TEST-public-key',
            'mercadopago.sandbox' => true,
            'mercadopago.webhook_secret' => 'signed-webhook-secret',
            'mercadopago.embedded.enabled' => true,
            'mercadopago.embedded.testing' => true,
            'mercadopago.embedded.authorized_event_slugs' => ['safe-by-varuna-1-edition'],
            'mercadopago.embedded.payment_path' => '/v1/payments',
            'meta.capi.enabled' => false,
        ]);

        Queue::fake();
    }

    public function test_signed_configuration_exposes_only_test_public_data_and_exact_total(): void
    {
        [$order] = $this->safeOrder();
        $url = URL::temporarySignedRoute(
            'tickets.mercadopago.embedded.configuration',
            now()->addMinutes(10),
            ['order' => $order],
        );

        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('public_key', 'TEST-public-key')
            ->assertJsonPath('amount', 105)
            ->assertJsonPath('currency', 'MXN')
            ->assertJsonPath('test_mode', true)
            ->assertJsonMissing(['access_token' => 'TEST-access-token']);

        $this->getJson(route('tickets.mercadopago.embedded.configuration', $order))
            ->assertForbidden();
    }

    public function test_brick_posts_only_a_token_and_provider_response_cannot_fulfil_tickets(): void
    {
        [$order] = $this->safeOrder();

        Http::fake([
            'api.mercadopago.com/v1/payments' => Http::response([
                'id' => 90001,
                'status' => 'in_process',
                'status_detail' => 'pending_contingency',
            ]),
        ]);

        $url = URL::temporarySignedRoute(
            'tickets.mercadopago.embedded.payment',
            now()->addMinutes(10),
            ['order' => $order],
        );
        $payload = [
            'token' => 'one-time-card-token',
            'payment_method_id' => 'master',
            'issuer_id' => '123',
            'installments' => 1,
            'payer' => ['identification' => ['type' => 'RFC', 'number' => 'TEST010101AA1']],
        ];

        $this->postJson($url, $payload)
            ->assertOk()
            ->assertJsonPath('fulfilment', 'pending_webhook_verification');

        $order->refresh();
        $this->assertSame('pending', $order->status);
        $this->assertSame('90001', $order->mp_payment_id);
        $this->assertSame(0, $order->attendees()->count());
        $this->assertStringNotContainsString('one-time-card-token', json_encode($order->metadata));

        Http::assertSent(function ($request) use ($order) {
            return $request->url() === 'https://api.mercadopago.com/v1/payments'
                && $request['transaction_amount'] === 105.0
                && $request['external_reference'] === $order->public_id
                && $request['payer']['email'] === 'buyer@example.com'
                && ! empty($request->header('X-Idempotency-Key'));
        });

        // A forged return URL is not payment authority for embedded orders.
        $this->get(route('tickets.success', $order).'?payment_id=90001')->assertOk();
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, $order->attendees()->count());

        // Reposting an accepted attempt is idempotent and does not call MP twice.
        $this->postJson($url, $payload)->assertOk();
        Http::assertSentCount(1);
    }

    public function test_pan_or_security_code_are_rejected_before_the_provider(): void
    {
        [$order] = $this->safeOrder();
        Http::fake();

        $url = URL::temporarySignedRoute(
            'tickets.mercadopago.embedded.payment',
            now()->addMinutes(10),
            ['order' => $order],
        );

        $this->postJson($url, [
            'token' => 'one-time-card-token',
            'payment_method_id' => 'visa',
            'installments' => 1,
            'card_number' => '4509953566233704',
            'security_code' => '123',
        ])->assertUnprocessable();

        Http::assertNothingSent();
    }

    public function test_embedded_flow_fails_closed_without_test_credentials(): void
    {
        [$order] = $this->safeOrder();
        config(['mercadopago.public_key' => 'APP-live-key']);

        $url = URL::temporarySignedRoute(
            'tickets.mercadopago.embedded.configuration',
            now()->addMinutes(10),
            ['order' => $order],
        );

        $this->withoutExceptionHandling();
        $this->expectException(\RuntimeException::class);
        $this->getJson($url);
    }

    public function test_only_a_valid_signed_webhook_with_exact_amount_issues_qr_and_pdf(): void
    {
        [$order, $product] = $this->safeOrder();
        $order->update([
            'mp_payment_id' => '90002',
            'mp_external_reference' => $order->public_id,
        ]);

        $payment = [
            'id' => 90002,
            'status' => 'approved',
            'status_detail' => 'accredited',
            'payment_method_id' => 'master',
            'transaction_amount' => 105,
            'currency_id' => 'MXN',
            'external_reference' => $order->public_id,
            'order' => ['id' => 'merchant-1'],
        ];

        Http::fake([
            'api.mercadopago.com/v1/payments/90002' => Http::response($payment),
        ]);

        $this->postJson(route('webhooks.mercadopago'), [
            'type' => 'payment',
            'data' => ['id' => '90002'],
        ])->assertUnauthorized();
        $this->assertSame('pending', $order->fresh()->status);

        $headers = $this->signedWebhookHeaders('90002');
        $this->postJson(route('webhooks.mercadopago'), [
            'type' => 'payment',
            'data' => ['id' => '90002'],
        ], $headers)->assertNoContent();

        $order = $order->fresh(['attendees.product', 'items.attendees', 'event']);
        $this->assertSame('paid', $order->status);
        $this->assertCount(1, $order->attendees);
        $this->assertSame(0, $product->fresh()->reserved_count);
        $this->assertSame(1, $product->fresh()->sold_count);

        $attendee = $order->attendees->first();
        $qr = $this->get($attendee->getCheckInQrUrl());
        $qr->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertStringStartsWith("\x89PNG", $qr->getContent());

        $pdf = app(TicketPassPdfService::class)->buildForAttendee($attendee)->output();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertSame('testing', data_get($attendee->product->metadata, 'sales_mode'));

        // Replayed webhook remains idempotent: no duplicate attendee or inventory commit.
        $this->postJson(route('webhooks.mercadopago'), [
            'type' => 'payment',
            'data' => ['id' => '90002'],
        ], $headers)->assertNoContent();
        $this->assertSame(1, $order->fresh()->attendees()->count());
        $this->assertSame(1, $product->fresh()->sold_count);

        $this->post($attendee->getCheckInConfirmUrl())
            ->assertRedirect($attendee->getCheckInUrl());
        $this->assertSame(0, $attendee->fresh()->check_in_count);
    }

    public function test_signed_approved_webhook_rejects_amount_or_currency_mismatch(): void
    {
        [$order] = $this->safeOrder();
        $order->update(['mp_payment_id' => '90003']);

        Http::fake([
            'api.mercadopago.com/v1/payments/90003' => Http::response([
                'id' => 90003,
                'status' => 'approved',
                'status_detail' => 'accredited',
                'transaction_amount' => 104.99,
                'currency_id' => 'MXN',
                'external_reference' => $order->public_id,
            ]),
        ]);

        $this->postJson(route('webhooks.mercadopago'), [
            'type' => 'payment',
            'data' => ['id' => '90003'],
        ], $this->signedWebhookHeaders('90003'))->assertStatus(422);

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, $order->attendees()->count());
    }

    public function test_abandoned_order_releases_inventory_but_provider_attempt_is_never_expired(): void
    {
        [$abandoned, $product] = $this->safeOrder();
        $abandoned->forceFill(['created_at' => now()->subHour()])->save();

        $submitted = app(TicketOrderService::class)->createOrder(
            $abandoned->event,
            [$product->id => 1],
            [
                'name' => 'Second Buyer',
                'email' => 'second@example.com',
                'phone' => '5512345679',
                'whatsapp' => '5512345679',
            ],
            ['payment_provider' => 'mercadopago'],
        );
        $submitted->update(['mp_payment_id' => 'provider-pending-1']);
        $submitted->forceFill(['created_at' => now()->subHour()])->save();

        $this->assertSame(2, $product->fresh()->reserved_count);

        $this->artisan('tickets:expire-abandoned-reservations', ['--minutes' => 30])
            ->expectsOutput('Expired 1 abandoned ticket reservation(s).')
            ->assertSuccessful();

        $this->assertSame('cancelled', $abandoned->fresh()->status);
        $this->assertSame('pending', $submitted->fresh()->status);
        $this->assertSame(1, $product->fresh()->reserved_count);
    }

    /** @return array{TicketOrder, TicketProduct} */
    private function safeOrder(): array
    {
        $this->artisan('events:register-safe-by-varuna-draft', [
            '--activate-testing' => true,
            '--confirm' => 'ACTIVATE_TESTING',
        ])->assertSuccessful();

        $event = Event::where('slug', 'safe-by-varuna-1-edition')->firstOrFail();
        $product = $event->ticketProducts()->firstOrFail();
        $order = app(TicketOrderService::class)->createOrder(
            $event,
            [$product->id => 1],
            [
                'name' => 'Buyer Test',
                'email' => 'buyer@example.com',
                'phone' => '5512345678',
                'whatsapp' => '5512345678',
            ],
            [
                'payment_provider' => 'mercadopago',
                'metadata' => [
                    'landing_url' => route('events.show', $event),
                    'checkout_event_id' => 'browser-checkout-event',
                ],
            ],
        );

        return [$order->fresh(['event', 'items.product']), $product];
    }

    /** @return array<string, string> */
    private function signedWebhookHeaders(string $dataId): array
    {
        $timestamp = '1787100000';
        $requestId = 'request-safe-test';
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp};";

        return [
            'x-request-id' => $requestId,
            'x-signature' => 'ts='.$timestamp.',v1='.hash_hmac('sha256', $manifest, 'signed-webhook-secret'),
        ];
    }
}
