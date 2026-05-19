<?php

namespace App\Http\Controllers;

use App\Models\TicketOrder;
use App\Services\StripeService;
use App\Services\TicketOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripeService $stripe, TicketOrderService $orderService): Response
    {
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

        $type = $event['type'] ?? '';
        $object = $event['data']['object'] ?? [];

        if (! is_array($object)) {
            return response()->noContent();
        }

        if ($type === 'checkout.session.completed') {
            $order = $this->resolveOrderFromSession($object);
            if (! $order) {
                return response()->noContent();
            }

            $orderService->syncStripeSession($order, $object);
        }

        if ($type === 'checkout.session.expired') {
            $order = $this->resolveOrderFromSession($object);
            if ($order) {
                $order->markAsCancelled([
                    'stripe_status' => $object['status'] ?? null,
                ]);
            }
        }

        if ($type === 'payment_intent.succeeded' || $type === 'payment_intent.payment_failed') {
            $order = $this->resolveOrderFromStripeObject($object);
            if ($order) {
                $orderService->syncStripePaymentIntent($order, $object);
            }
        }

        if ($type === 'charge.refunded') {
            $order = $this->resolveOrderFromStripeObject($object);
            if ($order) {
                $order->markAsRefunded([
                    'stripe_status' => $object['status'] ?? null,
                ]);
            }
        }

        return response()->noContent();
    }

    protected function resolveOrderFromSession(array $session): ?TicketOrder
    {
        $reference = $session['client_reference_id'] ?? null;
        if (! $reference) {
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
