<?php

namespace App\Services\Meta;

use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\TicketOrder;
use App\Support\Meta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaConversionsApiService
{
    public function isEnabled(): bool
    {
        return Meta::capiEnabled();
    }

    /**
     * @param  array<string, mixed>  $customData
     */
    public function sendLeadFromCustomer(Customer $customer, ?string $eventSourceUrl = null, array $customData = []): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $metadata = is_array($customer->metadata) ? $customer->metadata : [];

        $this->sendEvent(
            eventName: 'Lead',
            eventId: 'lead_customer_'.$customer->id,
            eventSourceUrl: $eventSourceUrl ?: (string) ($metadata['landing_url'] ?? $metadata['signup_page'] ?? config('app.url')),
            userData: $this->userDataFromCustomer($customer, $metadata),
            customData: array_filter(array_merge([
                'content_category' => 'lead_capture',
                'content_name' => $customer->source ?: 'popup',
            ], $customData)),
        );
    }

    public function sendInitiateCheckoutForBooking(ContentBooking $booking): void
    {
        if (! $this->isEnabled() || $this->isTestBooking($booking)) {
            return;
        }

        $this->sendEvent(
            eventName: 'InitiateCheckout',
            eventId: data_get($booking->metadata, 'checkout_event_id') ?: 'booking_checkout_'.$booking->public_id,
            eventSourceUrl: $booking->landing_url ?: config('app.url'),
            userData: $this->userDataFromBooking($booking),
            customData: $this->purchaseCustomData($booking),
        );
    }

    public function sendAddPaymentInfoForBooking(ContentBooking $booking): void
    {
        if (! $this->isEnabled() || $this->isTestBooking($booking)) {
            return;
        }

        $metadata = is_array($booking->metadata) ? $booking->metadata : [];

        $this->sendEvent(
            eventName: 'AddPaymentInfo',
            eventId: data_get($metadata, 'payment_info_event_id') ?: 'booking_payment_info_'.$booking->public_id,
            eventSourceUrl: $booking->landing_url ?: config('app.url'),
            userData: $this->userDataFromBooking($booking),
            customData: array_filter(array_merge(
                $this->purchaseCustomData($booking),
                ['payment_provider' => $booking->payment_provider],
            )),
        );
    }

    public function sendPurchaseForBooking(ContentBooking $booking): void
    {
        if (! $this->isEnabled() || $this->isTestBooking($booking)) {
            return;
        }

        // Idempotencia explícita: evita Purchase duplicado ante reintentos de webhook.
        if (data_get($booking->metadata, 'capi_purchase_sent')) {
            return;
        }

        $sent = $this->sendEvent(
            eventName: 'Purchase',
            eventId: 'booking_'.$booking->public_id,
            eventSourceUrl: $booking->landing_url ?: config('app.url'),
            userData: $this->userDataFromBooking($booking),
            customData: $this->purchaseCustomData($booking),
        );

        if (! $sent) {
            return;
        }

        $booking->forceFill([
            'metadata' => array_merge(
                is_array($booking->metadata) ? $booking->metadata : [],
                ['capi_purchase_sent' => true],
            ),
        ])->save();
    }

    public function sendPurchaseForTicketOrder(TicketOrder $order): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->sendEvent(
            eventName: 'Purchase',
            eventId: 'ticket_order_'.$order->public_id,
            eventSourceUrl: (string) data_get($order->metadata, 'landing_url', config('app.url')),
            userData: $this->userDataFromTicketOrder($order),
            customData: $this->ticketOrderCustomData($order),
        );
    }

    public function sendInitiateCheckoutForTicketOrder(TicketOrder $order): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->sendEvent(
            eventName: 'InitiateCheckout',
            eventId: (string) data_get($order->metadata, 'checkout_event_id', 'ticket_checkout_'.$order->public_id),
            eventSourceUrl: (string) data_get($order->metadata, 'landing_url', config('app.url')),
            userData: $this->userDataFromTicketOrder($order),
            customData: $this->ticketOrderCustomData($order),
        );
    }

    public function sendPaymentPendingForTicketOrder(TicketOrder $order): void
    {
        $this->sendTicketLifecycleEvent($order, 'PaymentPending', 'ticket_payment_pending_'.$order->public_id);
    }

    public function sendPaymentFailedForTicketOrder(TicketOrder $order): void
    {
        $this->sendTicketLifecycleEvent($order, 'PaymentFailed', 'ticket_payment_failed_'.$order->public_id);
    }

    public function sendPaymentPendingForBooking(ContentBooking $booking): void
    {
        $this->sendBookingLifecycleEvent($booking, 'PaymentPending', 'booking_payment_pending_'.$booking->public_id);
    }

    public function sendPaymentFailedForBooking(ContentBooking $booking): void
    {
        $this->sendBookingLifecycleEvent($booking, 'PaymentFailed', 'booking_payment_failed_'.$booking->public_id);
    }

    public function sendBookingAbandonedForBooking(ContentBooking $booking): void
    {
        $this->sendBookingLifecycleEvent($booking, 'BookingAbandoned', 'booking_abandoned_'.$booking->public_id);
    }

    /**
     * @param  array<string, mixed>  $userData
     * @param  array<string, mixed>  $customData
     */
    public function sendEvent(
        string $eventName,
        string $eventId,
        string $eventSourceUrl,
        array $userData,
        array $customData = [],
    ): bool {
        if (! $this->isEnabled()) {
            return false;
        }

        $pixelId = (string) Meta::pixelId();
        $version = (string) config('meta.marketing_api.api_version', 'v21.0');
        $url = "https://graph.facebook.com/{$version}/{$pixelId}/events";

        $payload = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'event_time' => now()->timestamp,
                    'event_id' => $eventId,
                    'event_source_url' => $eventSourceUrl,
                    'action_source' => 'website',
                    'user_data' => array_filter($userData),
                    'custom_data' => array_filter($customData),
                ],
            ],
            'access_token' => config('meta.marketing_api.access_token'),
        ];

        if ($testCode = config('meta.capi.test_event_code')) {
            $payload['test_event_code'] = $testCode;
        }

        try {
            $response = Http::timeout(15)->post($url, $payload);

            $eventsReceived = (int) $response->json('events_received', 0);

            if ($response->failed() || $eventsReceived < 1 || $response->json('error') !== null) {
                Log::warning('Meta CAPI event failed', [
                    'event' => $eventName,
                    'event_id' => $eventId,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI request exception', [
                'event' => $eventName,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    protected function userDataFromCustomer(Customer $customer, array $metadata = []): array
    {
        return array_filter([
            'em' => $this->hash($customer->email),
            'ph' => $this->hashPhone($customer->phone ?: $customer->whatsapp),
            'external_id' => $this->hash('customer_'.$customer->id),
            'client_ip_address' => $customer->ip_address,
            'client_user_agent' => $customer->user_agent,
            'fbc' => $metadata['fbc'] ?? null,
            'fbp' => $metadata['fbp'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function userDataFromBooking(ContentBooking $booking): array
    {
        $externalId = $booking->customer_id
            ? $this->hash('customer_'.$booking->customer_id)
            : ($booking->public_id ? $this->hash('booking_'.$booking->public_id) : null);

        [$firstName, $lastName] = $this->splitName($booking->client_name);

        return array_filter([
            'em' => $this->hash($booking->client_email),
            'ph' => $this->hashPhone($booking->client_phone),
            'fn' => $this->hash($firstName),
            'ln' => $this->hash($lastName),
            'external_id' => $externalId,
            'client_ip_address' => $booking->client_ip_address,
            'client_user_agent' => $booking->client_user_agent,
            'fbc' => $booking->fbc,
            'fbp' => $booking->fbp,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function userDataFromTicketOrder(TicketOrder $order): array
    {
        return array_filter([
            'em' => $this->hash($order->buyer_email),
            'ph' => $this->hashPhone($order->buyer_phone ?: $order->buyer_whatsapp),
            'external_id' => $this->hash('ticket_order_'.$order->public_id),
            'client_ip_address' => $order->ip_address,
            'client_user_agent' => $order->user_agent,
            'fbc' => data_get($order->metadata, 'fbc'),
            'fbp' => data_get($order->metadata, 'fbp'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function purchaseCustomData(ContentBooking $booking): array
    {
        return [
            'currency' => $booking->currency ?: 'MXN',
            'value' => (float) $booking->amount,
            'content_type' => 'product',
            'content_ids' => [(string) $booking->public_id],
            'content_name' => $booking->service_name,
            'content_category' => match ($booking->service_type) {
                ContentBooking::SERVICE_DJ_SET => 'dj_set_booking',
                ContentBooking::SERVICE_DRONE_SESSION => 'drone_session_booking',
                ContentBooking::SERVICE_CONSTRUCTION_PROGRESS => 'construction_progress_booking',
                ContentBooking::SERVICE_ELECTRONIC_EVENT_COVERAGE => 'electronic_event_coverage_booking',
                ContentBooking::SERVICE_MULTI_CAMERA => 'multi_camera_booking',
                default => 'content_booking',
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function ticketOrderCustomData(TicketOrder $order): array
    {
        $order->loadMissing(['event', 'items']);

        $productIds = $order->items
            ->pluck('ticket_product_id')
            ->filter()
            ->map(fn ($id): string => (string) $id)
            ->values()
            ->all();

        $contents = $order->items
            ->map(fn ($item): array => [
                'id' => (string) $item->ticket_product_id,
                'quantity' => (int) $item->quantity,
                'item_price' => (float) $item->unit_price,
            ])
            ->values()
            ->all();

        return [
            'currency' => $order->currency ?: 'MXN',
            'value' => (float) $order->total,
            'content_type' => 'product',
            'content_ids' => $productIds !== [] ? $productIds : [(string) $order->public_id],
            'contents' => $contents,
            'content_name' => $order->event?->title,
            'content_category' => 'ticket_order',
        ];
    }

    protected function sendTicketLifecycleEvent(TicketOrder $order, string $eventName, string $eventId): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $metadata = is_array($order->metadata) ? $order->metadata : [];
        $sentKey = 'capi_'.strtolower($eventName).'_sent';

        if (! empty($metadata[$sentKey])) {
            return;
        }

        $sent = $this->sendEvent(
            eventName: $eventName,
            eventId: $eventId,
            eventSourceUrl: (string) data_get($metadata, 'landing_url', config('app.url')),
            userData: $this->userDataFromTicketOrder($order),
            customData: $this->ticketOrderCustomData($order),
        );

        if (! $sent) {
            return;
        }

        $order->forceFill([
            'metadata' => array_merge($metadata, [$sentKey => true]),
        ])->save();
    }

    protected function sendBookingLifecycleEvent(ContentBooking $booking, string $eventName, string $eventId): void
    {
        if (! $this->isEnabled() || $this->isTestBooking($booking)) {
            return;
        }

        $metadata = is_array($booking->metadata) ? $booking->metadata : [];
        $sentKey = 'capi_'.strtolower($eventName).'_sent';

        if (! empty($metadata[$sentKey])) {
            return;
        }

        $sent = $this->sendEvent(
            eventName: $eventName,
            eventId: $eventId,
            eventSourceUrl: $booking->landing_url ?: config('app.url'),
            userData: $this->userDataFromBooking($booking),
            customData: $this->purchaseCustomData($booking),
        );

        if (! $sent) {
            return;
        }

        $booking->forceFill([
            'metadata' => array_merge($metadata, [$sentKey => true]),
        ])->save();
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    protected function splitName(?string $name): array
    {
        $clean = trim((string) $name);

        if ($clean === '') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $clean) ?: [];
        $first = array_shift($parts);
        $last = $parts !== [] ? implode(' ', $parts) : null;

        return [$first, $last];
    }

    protected function hash(?string $value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        return $normalized !== '' ? hash('sha256', $normalized) : null;
    }

    protected function hashPhone(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?: '';

        return $digits !== '' ? hash('sha256', $digits) : null;
    }

    protected function isTestBooking(ContentBooking $booking): bool
    {
        return (bool) data_get($booking->metadata, 'skip_payment_mode');
    }
}
