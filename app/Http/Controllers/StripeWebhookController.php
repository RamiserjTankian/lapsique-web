<?php

namespace App\Http\Controllers;

use App\Models\StripeWebhookEvent;
use App\Services\Stripe\StripeWebhookHandler;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function handle(
        Request $request,
        StripeService $stripe,
        StripeWebhookHandler $handler,
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
            $handler->handle($type, $object, $eventId !== '' ? $eventId : null);
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
}
