<?php

namespace App\Services\Stripe;

use App\Models\ContentBooking;
use App\Models\TicketOrder;
use App\Services\ContentBookingPaymentService;
use App\Services\StripeService;
use App\Services\TicketOrderService;
use Illuminate\Support\Facades\Log;

class StripeWebhookHandler
{
    public function __construct(
        protected StripeService $stripe,
        protected ContentBookingPaymentService $bookingPayment,
        protected TicketOrderService $orderService,
    ) {}

    public function handle(string $type, array $object, ?string $eventId = null): void
    {
        match (true) {
            in_array($type, [
                'checkout.session.completed',
                'checkout.session.async_payment_succeeded',
            ], true) => $this->handleCheckoutSessionSuccess($object, $eventId, $type),

            $type === 'checkout.session.async_payment_pending' => $this->handleCheckoutSessionPending($object, $eventId),

            in_array($type, [
                'checkout.session.expired',
            ], true) => $this->handleCheckoutSessionExpired($object, $eventId),

            $type === 'checkout.session.async_payment_failed' => $this->handleCheckoutSessionFailed($object, $eventId),

            in_array($type, [
                'payment_intent.succeeded',
                'payment_intent.payment_failed',
                'payment_intent.canceled',
            ], true) => $this->handlePaymentIntent($type, $object, $eventId),

            $type === 'charge.refunded' => $this->handleChargeRefunded($object, $eventId),

            $type === 'refund.updated' && (string) data_get($object, 'status') === 'succeeded' => $this->handleRefundUpdated($object, $eventId),

            default => $this->logIgnoredEvent($type, $eventId),
        };
    }

    protected function handleCheckoutSessionSuccess(array $session, ?string $eventId, string $type): void
    {
        $session = $this->normalizeCheckoutSession($session);

        if ($booking = $this->resolveBookingFromSession($session)) {
            $this->bookingPayment->syncStripeSession($booking, $session);

            return;
        }

        if ($order = $this->resolveOrderFromSession($session)) {
            $this->orderService->syncStripeSession($order, $session);

            return;
        }

        $this->logUnresolved($type, $eventId, $session);
    }

    protected function handleCheckoutSessionPending(array $session, ?string $eventId): void
    {
        $session = $this->normalizeCheckoutSession($session);

        if ($booking = $this->resolveBookingFromSession($session)) {
            $this->bookingPayment->syncStripeSession($booking, $session);

            return;
        }

        if ($order = $this->resolveOrderFromSession($session)) {
            $this->orderService->syncStripeSession($order, $session);

            return;
        }

        $this->logUnresolved('checkout.session.async_payment_pending', $eventId, $session);
    }

    protected function handleCheckoutSessionExpired(array $session, ?string $eventId): void
    {
        $payload = [
            'stripe_session_id' => (string) data_get($session, 'id'),
            'stripe_status' => (string) data_get($session, 'status', 'expired'),
        ];

        if ($booking = $this->resolveBookingFromSession($session)) {
            $this->stripe->assertCheckoutSessionMatchesBooking($booking, $session);
            $this->bookingPayment->releaseSlotIfFailed($booking, 'failed');

            return;
        }

        if ($order = $this->resolveOrderFromSession($session)) {
            $this->orderService->expireStripeCheckout($order, $payload);

            return;
        }

        $this->logUnresolved('checkout.session.expired', $eventId, $session);
    }

    protected function handleCheckoutSessionFailed(array $session, ?string $eventId): void
    {
        $payload = [
            'stripe_session_id' => (string) data_get($session, 'id'),
            'stripe_status' => (string) data_get($session, 'status', 'async_payment_failed'),
        ];

        if ($booking = $this->resolveBookingFromSession($session)) {
            $this->stripe->assertCheckoutSessionMatchesBooking($booking, $session);
            $this->bookingPayment->releaseSlotIfFailed($booking, 'failed');

            return;
        }

        if ($order = $this->resolveOrderFromSession($session)) {
            $this->orderService->failStripeCheckout($order, $payload);

            return;
        }

        $this->logUnresolved('checkout.session.async_payment_failed', $eventId, $session);
    }

    protected function handlePaymentIntent(string $type, array $intent, ?string $eventId): void
    {
        if ($booking = $this->resolveBookingFromPaymentIntent($intent)) {
            $this->bookingPayment->syncStripePaymentIntent($booking, $intent);

            return;
        }

        if ($order = $this->resolveOrderFromPaymentIntent($intent)) {
            $this->orderService->syncStripePaymentIntent($order, $intent);

            return;
        }

        $this->logUnresolved($type, $eventId, $intent);
    }

    protected function handleChargeRefunded(array $charge, ?string $eventId): void
    {
        if ($booking = $this->resolveBookingFromCharge($charge)) {
            $this->bookingPayment->syncStripeRefund($booking, $charge);

            return;
        }

        if ($order = $this->resolveOrderFromCharge($charge)) {
            $this->orderService->syncStripeRefund($order, $charge);

            return;
        }

        $this->logUnresolved('charge.refunded', $eventId, $charge);
    }

    protected function handleRefundUpdated(array $refund, ?string $eventId): void
    {
        $chargeId = (string) data_get($refund, 'charge', '');

        if ($chargeId === '') {
            Log::info('Stripe refund.updated without charge id', ['event_id' => $eventId]);

            return;
        }

        $this->handleChargeRefunded([
            'id' => $chargeId,
            'payment_intent' => data_get($refund, 'payment_intent'),
            'status' => 'refunded',
        ], $eventId);
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeCheckoutSession(array $session): array
    {
        $paymentIntent = data_get($session, 'payment_intent');

        if (! is_string($paymentIntent) || $paymentIntent === '' || ! str_starts_with($paymentIntent, 'pi_')) {
            return $session;
        }

        try {
            $fetched = $this->stripe->fetchPaymentIntent($paymentIntent);
            $session['payment_intent'] = $fetched;
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook could not expand payment_intent for session', [
                'session_id' => data_get($session, 'id'),
                'payment_intent' => $paymentIntent,
                'error' => $e->getMessage(),
            ]);
        }

        return $session;
    }

    protected function resolveBookingFromSession(array $session): ?ContentBooking
    {
        $reference = (string) ($session['client_reference_id'] ?? '');

        if (str_starts_with($reference, 'bkg_')) {
            $booking = ContentBooking::where('public_id', substr($reference, 4))->first();
            if ($booking) {
                return $booking;
            }
        }

        $metadataRef = (string) data_get($session, 'metadata.content_booking_public_id');
        if ($metadataRef !== '') {
            $booking = ContentBooking::where('public_id', $metadataRef)->first();
            if ($booking) {
                return $booking;
            }
        }

        $sessionId = (string) data_get($session, 'id');
        if ($sessionId !== '') {
            return ContentBooking::where('stripe_checkout_session_id', $sessionId)->first();
        }

        return null;
    }

    protected function resolveOrderFromSession(array $session): ?TicketOrder
    {
        $reference = $session['client_reference_id'] ?? null;
        if ($reference && ! str_starts_with((string) $reference, 'bkg_')) {
            $order = TicketOrder::where('public_id', $reference)->first();
            if ($order) {
                return $order;
            }
        }

        $metadataRef = (string) data_get($session, 'metadata.ticket_order_public_id');
        if ($metadataRef !== '') {
            $order = TicketOrder::where('public_id', $metadataRef)->first();
            if ($order) {
                return $order;
            }
        }

        $sessionId = (string) data_get($session, 'id');
        if ($sessionId !== '') {
            return TicketOrder::where('stripe_session_id', $sessionId)->first();
        }

        return null;
    }

    protected function resolveBookingFromPaymentIntent(array $intent): ?ContentBooking
    {
        $metadata = is_array($intent['metadata'] ?? null) ? $intent['metadata'] : [];
        $reference = (string) ($metadata['content_booking_public_id'] ?? '');

        if ($reference !== '') {
            $booking = ContentBooking::where('public_id', $reference)->first();
            if ($booking) {
                return $booking;
            }
        }

        $intentId = (string) ($intent['id'] ?? '');

        if ($intentId !== '') {
            return ContentBooking::where('stripe_payment_intent_id', $intentId)->first();
        }

        return null;
    }

    protected function resolveOrderFromPaymentIntent(array $intent): ?TicketOrder
    {
        $metadata = is_array($intent['metadata'] ?? null) ? $intent['metadata'] : [];
        $reference = (string) ($metadata['ticket_order_public_id'] ?? '');

        if ($reference !== '') {
            $order = TicketOrder::where('public_id', $reference)->first();
            if ($order) {
                return $order;
            }
        }

        $intentId = (string) ($intent['id'] ?? '');

        if ($intentId !== '') {
            return TicketOrder::where('stripe_payment_intent_id', $intentId)->first();
        }

        return null;
    }

    protected function resolveBookingFromCharge(array $charge): ?ContentBooking
    {
        $metadata = is_array($charge['metadata'] ?? null) ? $charge['metadata'] : [];
        $reference = (string) ($metadata['content_booking_public_id'] ?? '');

        if ($reference !== '') {
            $booking = ContentBooking::where('public_id', $reference)->first();
            if ($booking) {
                return $booking;
            }
        }

        $intentId = (string) data_get($charge, 'payment_intent', '');

        if ($intentId !== '') {
            return ContentBooking::where('stripe_payment_intent_id', $intentId)->first();
        }

        return null;
    }

    protected function resolveOrderFromCharge(array $charge): ?TicketOrder
    {
        $metadata = is_array($charge['metadata'] ?? null) ? $charge['metadata'] : [];
        $reference = (string) ($metadata['ticket_order_public_id'] ?? '');

        if ($reference !== '') {
            $order = TicketOrder::where('public_id', $reference)->first();
            if ($order) {
                return $order;
            }
        }

        $intentId = (string) data_get($charge, 'payment_intent', '');

        if ($intentId !== '') {
            return TicketOrder::where('stripe_payment_intent_id', $intentId)->first();
        }

        return null;
    }

    protected function logUnresolved(string $type, ?string $eventId, array $object): void
    {
        Log::warning('Stripe webhook could not resolve booking or order', [
            'event_id' => $eventId,
            'type' => $type,
            'client_reference_id' => data_get($object, 'client_reference_id'),
            'object_id' => data_get($object, 'id'),
            'metadata' => data_get($object, 'metadata'),
        ]);
    }

    protected function logIgnoredEvent(string $type, ?string $eventId): void
    {
        if ($type === '') {
            return;
        }

        Log::info('Stripe webhook event ignored', [
            'event_id' => $eventId,
            'type' => $type,
        ]);
    }
}
