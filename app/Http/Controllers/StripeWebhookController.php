<?php

namespace App\Http\Controllers;

use App\Models\ContentBooking;
use App\Models\StripeWebhookEvent;
use App\Models\TicketOrder;
use App\Services\ContentBookingPaymentService;
use App\Services\StripeService;
use App\Services\TicketOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function handle(
        Request $request,
        StripeService $stripe,
        TicketOrderService $orderService,
        ContentBookingPaymentService $bookingPaymentService,
    ): Response {
        $payload = $request->getContent();

        if (! $stripe->verifyWebhookSignature($request, $payload)) {
            Log::warning('Stripe webhook signature invalid', [
                'ip' => $request->ip(),
            ]);

            return response('Invalid signature', 401);
        }

        $event = json_decode($payload, true);
        if (! is_array($event)) {
            return response('Invalid payload', 400);
        }

        $eventId = (string) ($event['id'] ?? '');
        $type = (string) ($event['type'] ?? '');

        if ($eventId !== '' && StripeWebhookEvent::alreadyProcessed($eventId)) {
            return response()->noContent();
        }

        $webhookRecord = $eventId !== ''
            ? StripeWebhookEvent::recordReceived($eventId, $type, $event)
            : null;

        $object = $event['data']['object'] ?? [];

        if (! is_array($object)) {
            $webhookRecord?->markProcessed();

            return response()->noContent();
        }

        try {
            $this->dispatchEvent($type, $object, $orderService, $bookingPaymentService);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler failed', [
                'event_id' => $eventId,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $webhookRecord?->markProcessed();

        return response()->noContent();
    }

    protected function dispatchEvent(
        string $type,
        array $object,
        TicketOrderService $orderService,
        ContentBookingPaymentService $bookingPaymentService,
    ): void {
        if (in_array($type, [
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded',
        ], true)) {
            $booking = $this->resolveBookingFromSession($object);
            if ($booking) {
                $bookingPaymentService->syncStripeSession($booking, $object);

                return;
            }

            $order = $this->resolveOrderFromSession($object);
            if ($order) {
                $orderService->syncStripeSession($order, $object);
            }

            return;
        }

        if (in_array($type, [
            'checkout.session.expired',
            'checkout.session.async_payment_failed',
        ], true)) {
            $booking = $this->resolveBookingFromSession($object);
            if ($booking) {
                $bookingPaymentService->releaseSlotIfFailed($booking, 'failed');

                return;
            }

            $order = $this->resolveOrderFromSession($object);
            if ($order) {
                $order->markAsCancelled([
                    'stripe_status' => $object['status'] ?? null,
                ]);
            }

            return;
        }

        if ($type === 'payment_intent.succeeded' || $type === 'payment_intent.payment_failed') {
            $booking = $this->resolveBookingFromStripeObject($object);
            if ($booking) {
                $bookingPaymentService->syncStripePaymentIntent($booking, $object);

                return;
            }

            $order = $this->resolveOrderFromStripeObject($object);
            if ($order) {
                $orderService->syncStripePaymentIntent($order, $object);
            }

            return;
        }

        if ($type === 'charge.refunded') {
            $booking = $this->resolveBookingFromStripeObject($object);
            if ($booking) {
                $bookingPaymentService->releaseSlotIfFailed($booking, 'failed');

                return;
            }

            $order = $this->resolveOrderFromStripeObject($object);
            if ($order) {
                $order->markAsRefunded([
                    'stripe_status' => $object['status'] ?? null,
                ]);
            }
        }
    }

    protected function resolveBookingFromSession(array $session): ?ContentBooking
    {
        $reference = (string) ($session['client_reference_id'] ?? '');

        if (str_starts_with($reference, 'bkg_')) {
            return ContentBooking::where('public_id', substr($reference, 4))->first();
        }

        $metadataRef = (string) data_get($session, 'metadata.content_booking_public_id');

        if ($metadataRef !== '') {
            return ContentBooking::where('public_id', $metadataRef)->first();
        }

        return null;
    }

    protected function resolveBookingFromStripeObject(array $object): ?ContentBooking
    {
        $metadata = $object['metadata'] ?? [];
        $reference = (string) ($metadata['content_booking_public_id'] ?? '');

        if ($reference !== '') {
            return ContentBooking::where('public_id', $reference)->first();
        }

        $intentId = (string) ($object['payment_intent'] ?? $object['id'] ?? '');

        if ($intentId === '') {
            return null;
        }

        return ContentBooking::where('stripe_payment_intent_id', $intentId)->first();
    }

    protected function resolveOrderFromSession(array $session): ?TicketOrder
    {
        $reference = $session['client_reference_id'] ?? null;
        if (! $reference || str_starts_with((string) $reference, 'bkg_')) {
            return null;
        }

        return TicketOrder::where('public_id', $reference)->first();
    }

    protected function resolveOrderFromStripeObject(array $object): ?TicketOrder
    {
        $metadata = $object['metadata'] ?? [];
        $reference = $metadata['ticket_order_public_id'] ?? null;

        if (! $reference) {
            $intentId = $object['payment_intent'] ?? $object['id'] ?? null;
            if (! $intentId) {
                return null;
            }

            return TicketOrder::where('stripe_payment_intent_id', $intentId)->first();
        }

        return TicketOrder::where('public_id', $reference)->first();
    }
}
