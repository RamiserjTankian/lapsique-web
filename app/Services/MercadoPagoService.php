<?php

namespace App\Services;

use App\Models\ContentBooking;
use App\Models\PaymentGatewayConnection;
use App\Models\TicketOrder;
use App\Support\MercadoPagoEmbeddedCheckout;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class MercadoPagoService
{
    public function getConnection(): ?PaymentGatewayConnection
    {
        if (! $this->hasConnectionTable()) {
            return null;
        }

        return PaymentGatewayConnection::query()
            ->provider('mercadopago')
            ->first();
    }

    public function getConnectionSummary(): array
    {
        $connection = $this->getConnection();

        if ($connection?->isConnected()) {
            return [
                'connected' => true,
                'status' => $connection->status ?? 'connected',
                'mode' => 'oauth',
                'managed_by_env' => false,
                'status_label' => 'OAuth',
                'account_id' => $connection->account_id,
                'account_email' => $connection->account_email,
                'account_name' => $connection->account_name,
                'public_key' => $connection->public_key ?: config('mercadopago.public_key'),
                'connected_at' => $connection->connected_at,
                'last_synced_at' => $connection->last_synced_at,
                'expires_at' => $connection->expires_at,
                'last_error_message' => $connection->last_error_message,
                'webhook_url' => Route::has('webhooks.mercadopago')
                    ? route('webhooks.mercadopago')
                    : null,
                'redirect_uri' => $this->resolveRedirectUri(),
                'oauth_ready' => $this->hasOAuthCredentials(),
                'storage_ready' => true,
            ];
        }

        $fallbackToken = (string) config('mercadopago.access_token', '');

        if ($fallbackToken !== '') {
            $profile = $this->fetchCurrentAccountProfileSafely();

            return [
                'connected' => true,
                'status' => 'connected',
                'mode' => 'direct_token',
                'managed_by_env' => true,
                'status_label' => 'Token directo',
                'account_id' => $profile['id'] ?? null,
                'account_email' => $profile['email'] ?? 'Token configurado en .env',
                'account_name' => trim(implode(' ', array_filter([
                    $profile['first_name'] ?? null,
                    $profile['last_name'] ?? null,
                ]))) ?: ($profile['nickname'] ?? 'Cuenta por token directo'),
                'public_key' => config('mercadopago.public_key'),
                'connected_at' => null,
                'last_synced_at' => ! empty($profile) ? now() : null,
                'expires_at' => null,
                'last_error_message' => empty($profile) ? 'No se pudo consultar /users/me, pero el token directo sigue habilitado.' : null,
                'webhook_url' => Route::has('webhooks.mercadopago')
                    ? route('webhooks.mercadopago')
                    : null,
                'redirect_uri' => $this->resolveRedirectUri(),
                'oauth_ready' => $this->hasOAuthCredentials(),
                'storage_ready' => $this->hasConnectionTable(),
            ];
        }

        return [
            'connected' => false,
            'status' => 'disconnected',
            'mode' => 'disconnected',
            'managed_by_env' => false,
            'status_label' => 'Sin conexión',
            'account_id' => null,
            'account_email' => null,
            'account_name' => null,
            'public_key' => config('mercadopago.public_key'),
            'connected_at' => null,
            'last_synced_at' => null,
            'expires_at' => null,
            'last_error_message' => null,
            'webhook_url' => Route::has('webhooks.mercadopago')
                ? route('webhooks.mercadopago')
                : null,
            'redirect_uri' => $this->resolveRedirectUri(),
            'oauth_ready' => $this->hasOAuthCredentials(),
            'storage_ready' => $this->hasConnectionTable(),
        ];
    }

    public function buildAuthorizationUrl(string $state): string
    {
        $clientId = (string) config('mercadopago.client_id');

        if ($clientId === '') {
            throw new RuntimeException('MERCADOPAGO_CLIENT_ID no configurado.');
        }

        return config('mercadopago.oauth_authorize_url').'?'.http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'platform_id' => 'mp',
            'state' => $state,
            'redirect_uri' => $this->resolveRedirectUri(),
        ]);
    }

    public function exchangeAuthorizationCode(string $code): PaymentGatewayConnection
    {
        $response = Http::asForm()
            ->acceptJson()
            ->post(config('mercadopago.oauth_token_url'), [
                'grant_type' => 'authorization_code',
                'client_id' => $this->requireClientId(),
                'client_secret' => $this->requireClientSecret(),
                'code' => $code,
                'redirect_uri' => $this->resolveRedirectUri(),
            ]);

        if (! $response->successful()) {
            Log::error('MercadoPago OAuth exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('No se pudo completar la conexión con Mercado Pago.');
        }

        return $this->storeConnectionTokens((array) $response->json());
    }

    public function disconnect(): void
    {
        $connection = $this->getConnection();

        if (! $connection) {
            return;
        }

        $connection->update([
            'status' => 'disconnected',
            'access_token' => null,
            'refresh_token' => null,
            'public_key' => null,
            'token_type' => null,
            'scope' => null,
            'expires_at' => null,
            'connected_at' => null,
            'last_synced_at' => now(),
            'last_error_at' => null,
            'last_error_message' => null,
            'metadata' => array_merge($connection->metadata ?? [], [
                'disconnected_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    public function syncConnectionAccount(?PaymentGatewayConnection $connection = null): ?PaymentGatewayConnection
    {
        $connection = $connection ?? $this->resolveDatabaseConnection();

        if (! $connection) {
            if ((string) config('mercadopago.access_token', '') !== '') {
                $this->fetchCurrentAccountProfile();
            }

            return $connection;
        }

        if (! $connection->isConnected()) {
            return $connection;
        }

        try {
            $user = $this->httpClient()->get('/users/me');

            if (! $user->successful()) {
                throw new RuntimeException('No se pudo consultar la cuenta conectada.');
            }

            $payload = (array) $user->json();
            $connection->update([
                'account_id' => (string) ($payload['id'] ?? $connection->account_id),
                'account_email' => (string) ($payload['email'] ?? $connection->account_email),
                'account_name' => trim(implode(' ', array_filter([
                    $payload['first_name'] ?? null,
                    $payload['last_name'] ?? null,
                ]))) ?: ($payload['nickname'] ?? $connection->account_name),
                'last_synced_at' => now(),
                'last_error_at' => null,
                'last_error_message' => null,
                'metadata' => array_merge($connection->metadata ?? [], [
                    'mercadopago_user' => Arr::only($payload, [
                        'site_id',
                        'nickname',
                        'registration_date',
                        'country_id',
                    ]),
                ]),
            ]);
        } catch (\Throwable $exception) {
            $connection->update([
                'last_error_at' => now(),
                'last_error_message' => Str::limit($exception->getMessage(), 65535, ''),
            ]);

            throw $exception;
        }

        return $connection->fresh();
    }

    public function createPreferenceForOrder(TicketOrder $order): array
    {
        $items = $order->items->map(function ($item) use ($order) {
            return [
                'title' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'currency_id' => $order->currency,
                'category_id' => 'tickets',
            ];
        })->values()->all();

        $payload = [
            'items' => $items,
            'payer' => array_filter([
                'name' => $order->buyer_name,
                'email' => $order->buyer_email,
                'phone' => array_filter([
                    'number' => $order->buyer_phone ?: $order->buyer_whatsapp,
                ]),
            ]),
            'external_reference' => $order->public_id,
            'statement_descriptor' => config('mercadopago.statement_descriptor'),
            'metadata' => [
                'ticket_order_id' => $order->id,
                'ticket_order_public_id' => $order->public_id,
            ],
            'back_urls' => [
                'success' => route('tickets.success', $order),
                'pending' => route('tickets.pending', $order),
                'failure' => route('tickets.failure', $order),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('webhooks.mercadopago'),
        ];

        $response = $this->httpClient()
            ->post('/checkout/preferences', $payload);

        if (! $response->successful()) {
            Log::error('MercadoPago preference error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $order->id,
            ]);

            throw new RuntimeException('No se pudo crear la preferencia de pago.');
        }

        return (array) $response->json();
    }

    /**
     * Public, non-sensitive setup for Mercado Pago's Card Payment Brick.
     *
     * @return array{public_key:string,amount:float,currency:string,sdk_url:string,test_mode:bool}
     */
    public function embeddedConfigurationForOrder(TicketOrder $order): array
    {
        $order = $this->assertEmbeddedOrder($order);
        $publicKey = trim((string) config('mercadopago.public_key', ''));

        // Resolve the access token here as part of preflight. It is never
        // returned to the browser, but a missing/mismatched token must fail before
        // the Brick becomes available.
        $this->requireEmbeddedAccessToken();

        return [
            'public_key' => $publicKey,
            'amount' => round((float) $order->total, 2),
            'currency' => strtoupper((string) $order->currency),
            'sdk_url' => (string) config('mercadopago.embedded.sdk_url'),
            'test_mode' => MercadoPagoEmbeddedCheckout::salesMode() === 'testing',
        ];
    }

    /**
     * Creates a provider payment using only Mercado Pago's one-time token.
     * A browser response never fulfils tickets; only the signed webhook may
     * fetch and reconcile the provider payment.
     *
     * @param  array<string, mixed>  $browserPayload
     * @return array{id:string,status:string,status_detail:?string}
     */
    public function createEmbeddedPayment(TicketOrder $order, array $browserPayload): array
    {
        $order = $this->assertEmbeddedOrder($order);
        $attempt = $this->embeddedAttemptFor($order);

        if ($attempt['payment_id'] !== null) {
            return [
                'id' => $attempt['payment_id'],
                'status' => $attempt['status'],
                'status_detail' => $attempt['status_detail'],
            ];
        }

        $payerIdentification = Arr::only((array) data_get($browserPayload, 'payer.identification', []), ['type', 'number']);
        $payload = array_filter([
            'token' => (string) ($browserPayload['token'] ?? ''),
            'transaction_amount' => round((float) $order->total, 2),
            'installments' => (int) ($browserPayload['installments'] ?? 0),
            'payment_method_id' => (string) ($browserPayload['payment_method_id'] ?? ''),
            'issuer_id' => filled($browserPayload['issuer_id'] ?? null) ? (string) $browserPayload['issuer_id'] : null,
            'payer' => array_filter([
                'email' => (string) $order->buyer_email,
                'identification' => array_filter($payerIdentification),
            ]),
            'description' => Str::limit('Acceso · '.$order->event()->value('title'), 250, ''),
            'external_reference' => (string) $order->public_id,
            'statement_descriptor' => config('mercadopago.statement_descriptor'),
            'notification_url' => route('webhooks.mercadopago'),
            'metadata' => [
                'ticket_order_id' => (string) $order->getKey(),
                'ticket_order_public_id' => (string) $order->public_id,
                'checkout' => 'card_payment_brick',
                'sales_mode' => MercadoPagoEmbeddedCheckout::salesMode(),
            ],
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        // Never log this request: it contains a single-use card token.
        $response = $this->embeddedHttpClient()
            ->withHeaders(['X-Idempotency-Key' => $attempt['idempotency_key']])
            ->post((string) config('mercadopago.embedded.payment_path', '/v1/payments'), $payload);

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo iniciar el pago con tarjeta.', $response->status());
        }

        $providerPayment = (array) $response->json();
        $paymentId = trim((string) data_get($providerPayment, 'id'));
        $status = trim((string) data_get($providerPayment, 'status'));

        if ($paymentId === '' || $status === '') {
            throw new RuntimeException('Mercado Pago no devolvió un estado de pago válido.');
        }

        $statusDetail = filled(data_get($providerPayment, 'status_detail'))
            ? (string) data_get($providerPayment, 'status_detail')
            : null;

        DB::transaction(function () use ($order, $attempt, $paymentId, $status, $statusDetail): void {
            $lockedOrder = TicketOrder::query()->lockForUpdate()->findOrFail($order->getKey());
            $metadata = $lockedOrder->metadata ?? [];
            $embeddedMetadata = (array) data_get($metadata, 'mercadopago_embedded', []);
            $metadata['mercadopago_embedded'] = array_filter([
                'idempotency_key' => $attempt['idempotency_key'],
                'created_at' => $attempt['created_at'],
                'payment_id' => $paymentId,
                'status' => $status,
                'status_detail' => $statusDetail,
                'previous_payment_ids' => $embeddedMetadata['previous_payment_ids'] ?? null,
            ], static fn (mixed $value): bool => $value !== null);

            $lockedOrder->update([
                'mp_payment_id' => $paymentId,
                'mp_external_reference' => (string) $lockedOrder->public_id,
                'metadata' => $metadata,
            ]);
        });

        return ['id' => $paymentId, 'status' => $status, 'status_detail' => $statusDetail];
    }

    public function createPreferenceForBooking(ContentBooking $booking): array
    {
        $slot = $booking->slot;
        $dateLabel = $slot
            ? $slot->date->translatedFormat('d \d\e F, Y').' a las '.$slot->time_label
            : 'por confirmar';

        $payload = [
            'items' => [
                [
                    'title' => $booking->service_stripe_name,
                    'description' => 'Sesión profesional el '.$dateLabel,
                    'quantity' => 1,
                    'unit_price' => (float) $booking->amount,
                    'currency_id' => $booking->currency,
                    'category_id' => 'services',
                ],
            ],
            'payer' => array_filter([
                'name' => $booking->client_name,
                'email' => $booking->client_email,
                'phone' => array_filter(['number' => $booking->client_phone]),
            ]),
            'external_reference' => 'bkg_'.$booking->public_id,
            'statement_descriptor' => config('mercadopago.statement_descriptor', 'Lapsique'),
            'metadata' => [
                'content_booking_id' => $booking->id,
                'content_booking_public_id' => $booking->public_id,
            ],
            'back_urls' => [
                'success' => route('booking.confirm', $booking->public_id),
                'pending' => route('booking.pending', $booking->public_id),
                'failure' => route('booking.failure', $booking->public_id),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('webhooks.mercadopago'),
        ];

        $response = $this->httpClient()->post('/checkout/preferences', $payload);

        if (! $response->successful()) {
            Log::error('MercadoPago booking preference error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'booking_id' => $booking->id,
            ]);

            throw new RuntimeException('No se pudo crear la preferencia de pago.');
        }

        return (array) $response->json();
    }

    public function fetchPayment(string $paymentId): array
    {
        $response = $this->httpClient()
            ->get('/v1/payments/'.$paymentId);

        if (! $response->successful()) {
            Log::warning('MercadoPago payment fetch failed', [
                'payment_id' => $paymentId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('No pudimos consultar el pago.');
        }

        return (array) $response->json();
    }

    public function fetchMerchantOrder(string $merchantOrderId): array
    {
        $response = $this->httpClient()
            ->get('/merchant_orders/'.$merchantOrderId);

        if (! $response->successful()) {
            Log::warning('MercadoPago merchant order fetch failed', [
                'merchant_order_id' => $merchantOrderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('No pudimos consultar la orden de Mercado Pago.');
        }

        return (array) $response->json();
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = (string) config('mercadopago.webhook_secret');

        if ($secret === '') {
            // Legacy redirect checkouts keep their historical behavior, but
            // the embedded flow must never acknowledge an unsigned webhook.
            return ! (bool) config('mercadopago.embedded.enabled', false);
        }

        $signature = (string) $request->header('x-signature', '');
        $requestId = (string) $request->header('x-request-id', '');

        if ($signature === '' || $requestId === '') {
            return false;
        }

        $parts = [];
        foreach (preg_split('/[,;]/', $signature) as $segment) {
            [$key, $value] = array_map('trim', explode('=', $segment, 2) + [null, null]);
            if ($key && $value) {
                $parts[$key] = $value;
            }
        }

        $timestamp = $parts['ts'] ?? null;
        $hash = $parts['v1'] ?? null;
        $dataId = $this->webhookDataId($request);

        if (! $timestamp || ! $hash || ! $dataId) {
            return false;
        }

        if (preg_match('/^[A-Za-z0-9]+$/', $dataId) === 1) {
            $dataId = strtolower($dataId);
        }

        $manifest = sprintf('id:%s;request-id:%s;ts:%s;', $dataId, $requestId, $timestamp);
        $calculated = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($calculated, $hash);
    }

    protected function webhookDataId(Request $request): ?string
    {
        $query = $request->query();
        $queryDataId = $query['data.id'] ?? $query['data_id'] ?? null;
        $body = $request->json()->all();
        if (! is_array($body) || $body === []) {
            $body = $request->all();
        }
        $bodyDataId = is_array($body)
            ? (data_get($body, 'data.id') ?? $body['data.id'] ?? $body['data_id'] ?? null)
            : null;

        if ($queryDataId !== null && $bodyDataId !== null
            && (string) $queryDataId !== (string) $bodyDataId) {
            return null;
        }

        return ($queryDataId ?? $bodyDataId) !== null
            ? (string) ($queryDataId ?? $bodyDataId)
            : null;
    }

    protected function storeConnectionTokens(array $payload, ?PaymentGatewayConnection $connection = null): PaymentGatewayConnection
    {
        if (! $this->hasConnectionTable()) {
            throw new RuntimeException('Falta la tabla payment_gateway_connections. Ejecuta las migraciones para habilitar OAuth persistente.');
        }

        $connection = $connection ?? PaymentGatewayConnection::query()->firstOrNew([
            'provider' => 'mercadopago',
        ]);

        $expiresIn = (int) ($payload['expires_in'] ?? 0);
        $expiresAt = $expiresIn > 0 ? now()->addSeconds($expiresIn) : null;

        $connection->fill([
            'status' => 'connected',
            'account_id' => (string) ($payload['user_id'] ?? $connection->account_id),
            'access_token' => $payload['access_token'] ?? $connection->access_token,
            'refresh_token' => $payload['refresh_token'] ?? $connection->refresh_token,
            'public_key' => $payload['public_key'] ?? $connection->public_key,
            'token_type' => $payload['token_type'] ?? $connection->token_type,
            'scope' => $payload['scope'] ?? $connection->scope,
            'expires_at' => $expiresAt,
            'connected_at' => $connection->connected_at ?? now(),
            'last_synced_at' => now(),
            'last_error_at' => null,
            'last_error_message' => null,
            'metadata' => array_merge($connection->metadata ?? [], [
                'oauth_payload' => Arr::only($payload, [
                    'user_id',
                    'scope',
                    'live_mode',
                ]),
            ]),
        ]);

        $connection->save();

        return $this->syncConnectionAccount($connection) ?? $connection->fresh();
    }

    protected function refreshConnectionToken(PaymentGatewayConnection $connection): PaymentGatewayConnection
    {
        if (! $connection->refresh_token) {
            throw new RuntimeException('La conexión de Mercado Pago no tiene refresh token.');
        }

        $response = Http::asForm()
            ->acceptJson()
            ->post(config('mercadopago.oauth_token_url'), [
                'grant_type' => 'refresh_token',
                'client_id' => $this->requireClientId(),
                'client_secret' => $this->requireClientSecret(),
                'refresh_token' => $connection->refresh_token,
            ]);

        if (! $response->successful()) {
            Log::error('MercadoPago OAuth refresh failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $connection->update([
                'last_error_at' => now(),
                'last_error_message' => 'No se pudo refrescar el token de Mercado Pago.',
            ]);

            throw new RuntimeException('No se pudo refrescar la conexión de Mercado Pago.');
        }

        return $this->storeConnectionTokens((array) $response->json(), $connection);
    }

    protected function resolveDatabaseConnection(): ?PaymentGatewayConnection
    {
        $connection = $this->getConnection();

        if (! $connection || ! $connection->isConnected()) {
            return null;
        }

        if ($connection->isExpired()) {
            return $this->refreshConnectionToken($connection);
        }

        return $connection;
    }

    protected function fetchCurrentAccountProfile(): array
    {
        $user = $this->httpClient()->get('/users/me');

        if (! $user->successful()) {
            throw new RuntimeException('No se pudo consultar la cuenta conectada.');
        }

        return (array) $user->json();
    }

    protected function fetchCurrentAccountProfileSafely(): array
    {
        try {
            return $this->fetchCurrentAccountProfile();
        } catch (\Throwable $exception) {
            Log::warning('MercadoPago account sync via direct token failed', [
                'message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    protected function httpClient()
    {
        return Http::withToken($this->requireAccessToken())
            ->acceptJson()
            ->baseUrl(rtrim((string) config('mercadopago.api_base_url'), '/'));
    }

    protected function embeddedHttpClient()
    {
        return Http::withToken($this->requireEmbeddedAccessToken())
            ->acceptJson()
            ->baseUrl(rtrim((string) config('mercadopago.api_base_url'), '/'));
    }

    protected function requireEmbeddedAccessToken(): string
    {
        $token = trim((string) config('mercadopago.access_token', ''));
        $testing = (bool) config('mercadopago.embedded.testing', true);
        $sandbox = (bool) config('mercadopago.sandbox', false);

        if ($token === ''
            || ($testing && (! $sandbox || ! str_starts_with($token, 'TEST-')))
            || (! $testing && ($sandbox || str_starts_with($token, 'TEST-')))) {
            throw new RuntimeException('Las credenciales de Mercado Pago no corresponden al modo de venta configurado.');
        }

        return $token;
    }

    private function assertEmbeddedOrder(TicketOrder $order): TicketOrder
    {
        $order = TicketOrder::query()->with(['event', 'items.product'])->find($order->getKey());

        if (! $order || ! MercadoPagoEmbeddedCheckout::isEligible($order)) {
            throw new RuntimeException('La orden no es elegible para pago embebido.');
        }

        return $order;
    }

    /**
     * @return array{idempotency_key:string,created_at:string,payment_id:?string,status:string,status_detail:?string}
     */
    private function embeddedAttemptFor(TicketOrder $order): array
    {
        return DB::transaction(function () use ($order): array {
            $lockedOrder = TicketOrder::query()->lockForUpdate()->findOrFail($order->getKey());
            $metadata = $lockedOrder->metadata ?? [];
            $existing = (array) data_get($metadata, 'mercadopago_embedded', []);
            $idempotencyKey = trim((string) ($existing['idempotency_key'] ?? ''));
            $createdAt = trim((string) ($existing['created_at'] ?? ''));
            $existingPaymentId = trim((string) ($existing['payment_id'] ?? ''));
            $existingStatus = strtolower(trim((string) ($existing['status'] ?? 'pending')));

            if ($existingPaymentId !== '' && in_array($existingStatus, ['rejected', 'cancelled'], true)) {
                $previousPaymentIds = collect((array) ($existing['previous_payment_ids'] ?? []))
                    ->map(fn (mixed $value): string => trim((string) $value))
                    ->filter()
                    ->push($existingPaymentId)
                    ->unique()
                    ->take(-10)
                    ->values()
                    ->all();
                $idempotencyKey = (string) Str::uuid();
                $createdAt = now()->toIso8601String();
                $metadata['mercadopago_embedded'] = [
                    'idempotency_key' => $idempotencyKey,
                    'created_at' => $createdAt,
                    'previous_payment_ids' => $previousPaymentIds,
                ];
                $lockedOrder->update([
                    'mp_payment_id' => null,
                    'mp_external_reference' => null,
                    'metadata' => $metadata,
                ]);
                $existingPaymentId = '';
                $existingStatus = 'pending';
            }

            if ($idempotencyKey === '') {
                $idempotencyKey = (string) Str::uuid();
                $createdAt = now()->toIso8601String();
                $metadata['mercadopago_embedded'] = [
                    'idempotency_key' => $idempotencyKey,
                    'created_at' => $createdAt,
                ];
                $lockedOrder->update(['metadata' => $metadata]);
            }

            return [
                'idempotency_key' => $idempotencyKey,
                'created_at' => $createdAt,
                'payment_id' => $existingPaymentId !== '' ? $existingPaymentId : null,
                'status' => $existingStatus,
                'status_detail' => filled($existing['status_detail'] ?? null) ? (string) $existing['status_detail'] : null,
            ];
        });
    }

    protected function requireAccessToken(): string
    {
        $connection = $this->resolveDatabaseConnection();

        if ($connection?->access_token) {
            return $connection->access_token;
        }

        $token = (string) config('mercadopago.access_token', '');

        if ($token === '') {
            throw new RuntimeException('Mercado Pago no está conectado. Configura OAuth o MERCADOPAGO_ACCESS_TOKEN.');
        }

        return $token;
    }

    protected function requireClientId(): string
    {
        $clientId = (string) config('mercadopago.client_id', '');

        if ($clientId === '') {
            throw new RuntimeException('MERCADOPAGO_CLIENT_ID no configurado.');
        }

        return $clientId;
    }

    protected function requireClientSecret(): string
    {
        $clientSecret = (string) config('mercadopago.client_secret', '');

        if ($clientSecret === '') {
            throw new RuntimeException('MERCADOPAGO_CLIENT_SECRET no configurado.');
        }

        return $clientSecret;
    }

    protected function hasOAuthCredentials(): bool
    {
        return (string) config('mercadopago.client_id', '') !== ''
            && (string) config('mercadopago.client_secret', '') !== '';
    }

    protected function resolveRedirectUri(): string
    {
        $configured = (string) config('mercadopago.redirect_uri', '');

        if ($configured !== '') {
            return $configured;
        }

        return route('mercadopago.oauth.callback');
    }

    protected function hasConnectionTable(): bool
    {
        return Schema::hasTable((new PaymentGatewayConnection)->getTable());
    }
}
