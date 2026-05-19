<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TicketPendingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_page_shows_retry_payment_action(): void
    {
        [$event, $order] = $this->createPendingOrder();

        $response = $this->get(route('tickets.pending', $order));

        $response->assertOk();
        $response->assertSee('Estamos validando tu pago');
        $response->assertSee('Volver a pagar');
        $response->assertSee('Ver estado de mi compra');
        $response->assertSee($event->title);
    }

    public function test_retry_payment_reopens_mercadopago_checkout_for_pending_order(): void
    {
        config()->set('mercadopago.access_token', 'mp-test-token');
        config()->set('mercadopago.api_base_url', 'https://api.mercadopago.test');
        config()->set('mercadopago.sandbox', false);

        [, $order] = $this->createPendingOrder([
            'payment_provider' => 'mercadopago',
        ]);

        Http::fake([
            'https://api.mercadopago.test/checkout/preferences' => Http::response([
                'id' => 'pref_retry_123',
                'init_point' => 'https://checkout.mercadopago.test/pay/retry-123',
                'sandbox_init_point' => 'https://sandbox.mercadopago.test/pay/retry-123',
            ], 200),
        ]);

        $response = $this->post(route('tickets.retry', $order));

        $response->assertRedirect('https://checkout.mercadopago.test/pay/retry-123');

        $order->refresh();

        $this->assertSame('pref_retry_123', $order->mp_preference_id);
        $this->assertSame('https://checkout.mercadopago.test/pay/retry-123', data_get($order->metadata, 'mp_init_point'));
        $this->assertNotNull(data_get($order->metadata, 'checkout_retried_at'));
    }

    public function test_retry_payment_reopens_stripe_checkout_for_pending_order(): void
    {
        config()->set('stripe.secret_key', 'sk_test_123');

        [, $order] = $this->createPendingOrder([
            'payment_provider' => 'stripe',
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_retry_123',
                'status' => 'open',
                'url' => 'https://checkout.stripe.test/session/retry-123',
            ], 200),
        ]);

        $response = $this->post(route('tickets.retry', $order));

        $response->assertRedirect('https://checkout.stripe.test/session/retry-123');

        $order->refresh();

        $this->assertSame('cs_retry_123', $order->stripe_session_id);
        $this->assertSame('open', $order->stripe_status);
        $this->assertSame('https://checkout.stripe.test/session/retry-123', data_get($order->metadata, 'stripe_checkout_url'));
        $this->assertNotNull(data_get($order->metadata, 'checkout_retried_at'));
    }

    protected function createPendingOrder(array $overrides = []): array
    {
        $event = Event::create([
            'title' => 'REBOLLEDO',
            'slug' => 'rebolledo-pending-test',
            'venue' => 'Zal Marina',
            'city' => 'Progreso, Yucatán',
        ]);

        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'Mesa Frente al DJ',
            'category' => 'table',
            'currency' => 'MXN',
            'price' => 8280,
            'service_charge_pct' => 15,
            'access_units' => 6,
            'check_in_limit' => 1,
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = TicketOrder::create(array_merge([
            'event_id' => $event->id,
            'status' => 'pending',
            'payment_provider' => 'mercadopago',
            'currency' => 'MXN',
            'subtotal' => 14400,
            'fee' => 2160,
            'total' => 16560,
            'items_quantity' => 2,
            'attendees_expected' => 12,
            'attendees_registered' => 0,
            'buyer_name' => 'Ramiro Diaz Ramos',
            'buyer_email' => 'ramiro@bluepointrs.com',
            'buyer_phone' => '7444372758',
            'buyer_whatsapp' => '7444372758',
            'metadata' => ['reservation_status' => 'reserved'],
        ], $overrides));

        TicketOrderItem::create([
            'ticket_order_id' => $order->id,
            'ticket_product_id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'quantity' => 2,
            'unit_price' => 8280,
            'total_price' => 16560,
            'access_units' => 6,
            'check_in_limit' => 1,
            'metadata' => [
                'unit_base_price' => 7200,
                'unit_fee' => 1080,
                'line_subtotal' => 14400,
                'line_fee' => 2160,
            ],
        ]);

        return [$event, $order];
    }
}
