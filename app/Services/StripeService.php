<?php

namespace App\Services;

use App\Models\ContentBooking;
use App\Models\TicketOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StripeService
{
    public function __construct(
        protected ?StripeIntegrationService $integration = null,
    ) {
        $this->integration ??= app(StripeIntegrationService::class);
    }

    public function createCheckoutSession(TicketOrder $order): array
    {
        $secret = $this->requireSecretKey();

        $lineItems = [];
        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    'unit_amount' => (int) round(((float) $item->unit_price) * 100),
                ],
                'quantity' => $item->quantity,
            ];
        }

        $payload = [
            'mode' => 'payment',
            'client_reference_id' => $order->public_id,
            'customer_email' => $order->buyer_email,
            'success_url' => route('tickets.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('tickets.failure', $order),
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'metadata' => [
                'ticket_order_id' => $order->id,
                'ticket_order_public_id' => $order->public_id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'ticket_order_id' => $order->id,
                    'ticket_order_public_id' => $order->public_id,
                ],
            ],
        ];

        $response = Http::withToken($secret)
            ->asForm()
            ->acceptJson()
            ->post('https://api.stripe.com/v1/checkout/sessions', $payload);

        if (! $response->successful()) {
            Log::error('Stripe checkout session error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $order->id,
            ]);

            throw new RuntimeException('No se pudo crear la sesion de pago en Stripe.');
        }

        return (array) $response->json();
    }

    public function createCheckoutSessionForBooking(ContentBooking $booking): array
    {
        $secret = $this->requireSecretKey();
        $booking->loadMissing('slot');

        $slot = $booking->slot;
        $dateLabel = $slot
            ? $slot->date->translatedFormat('d \d\e F, Y').' a las '.$slot->time_label
            : 'por confirmar';

        $payload = [
            'mode' => 'payment',
            'client_reference_id' => 'bkg_'.$booking->public_id,
            'customer_email' => $booking->client_email,
            'success_url' => route('booking.confirm', $booking->public_id).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('booking.failure', $booking->public_id),
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => strtolower($booking->currency),
                        'product_data' => [
                            'name' => $booking->service_stripe_name,
                            'description' => $booking->service_description.' el '.$dateLabel,
                        ],
                        'unit_amount' => (int) $booking->amount * 100,
                    ],
                    'quantity' => 1,
                ],
            ],
            'metadata' => [
                'content_booking_id' => (string) $booking->id,
                'content_booking_public_id' => $booking->public_id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'content_booking_id' => (string) $booking->id,
                    'content_booking_public_id' => $booking->public_id,
                ],
            ],
        ];

        $response = Http::withToken($secret)
            ->asForm()
            ->acceptJson()
            ->post('https://api.stripe.com/v1/checkout/sessions', $payload);

        if (! $response->successful()) {
            Log::error('Stripe booking checkout session error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'booking_id' => $booking->id,
            ]);

            throw new RuntimeException('No se pudo crear la sesion de pago en Stripe.');
        }

        return (array) $response->json();
    }

    public function fetchSession(string $sessionId): array
    {
        $secret = $this->requireSecretKey();

        $response = Http::withToken($secret)
            ->acceptJson()
            ->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId, [
                'expand' => ['payment_intent'],
            ]);

        if (! $response->successful()) {
            Log::warning('Stripe session fetch failed', [
                'session_id' => $sessionId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('No pudimos consultar la sesion de pago.');
        }

        return (array) $response->json();
    }

    public function fetchPaymentIntent(string $paymentIntentId): array
    {
        $secret = $this->requireSecretKey();

        $response = Http::withToken($secret)
            ->acceptJson()
            ->get('https://api.stripe.com/v1/payment_intents/'.$paymentIntentId);

        if (! $response->successful()) {
            Log::warning('Stripe payment intent fetch failed', [
                'payment_intent_id' => $paymentIntentId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('No pudimos consultar el payment intent.');
        }

        return (array) $response->json();
    }

    public function verifyWebhookSignature(Request $request, string $payload): bool
    {
        $secret = (string) ($this->integration->resolveWebhookSecret() ?? '');

        if ($secret === '') {
            return true;
        }

        $signature = (string) $request->header('stripe-signature', '');

        if ($signature === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signature) as $segment) {
            [$key, $value] = array_map('trim', explode('=', $segment, 2) + [null, null]);
            if ($key && $value) {
                $parts[$key] = $value;
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signatureHash = $parts['v1'] ?? null;

        if (! $timestamp || ! $signatureHash) {
            return false;
        }

        $tolerance = $this->integration->resolveWebhookToleranceSeconds();
        if ($tolerance > 0 && abs(time() - (int) $timestamp) > $tolerance) {
            Log::warning('Stripe webhook timestamp outside tolerance', [
                'timestamp' => $timestamp,
                'tolerance' => $tolerance,
            ]);

            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $signatureHash);
    }

    protected function requireSecretKey(): string
    {
        $secret = $this->integration->resolveSecretKey();

        if (! filled($secret)) {
            throw new RuntimeException('Stripe no configurado: agrega la Secret Key en el panel o STRIPE_SECRET_KEY en .env.');
        }

        return (string) $secret;
    }
}
