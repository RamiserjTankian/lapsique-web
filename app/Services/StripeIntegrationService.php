<?php

namespace App\Services;

use App\Models\StripeSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class StripeIntegrationService
{
    public function resolveSecretKey(): ?string
    {
        $fromDatabase = StripeSetting::current()?->secret_key;

        if (filled($fromDatabase)) {
            return (string) $fromDatabase;
        }

        $fromEnv = (string) config('stripe.secret_key', '');

        return $fromEnv !== '' ? $fromEnv : null;
    }

    public function resolvePublishableKey(): ?string
    {
        $fromDatabase = StripeSetting::current()?->publishable_key;

        if (filled($fromDatabase)) {
            return (string) $fromDatabase;
        }

        $fromEnv = (string) config('stripe.publishable_key', '');

        return $fromEnv !== '' ? $fromEnv : null;
    }

    public function resolveWebhookSecret(): ?string
    {
        $fromDatabase = StripeSetting::current()?->webhook_secret;

        if (filled($fromDatabase)) {
            return (string) $fromDatabase;
        }

        $fromEnv = (string) config('stripe.webhook_secret', '');

        return $fromEnv !== '' ? $fromEnv : null;
    }

    public function resolveCurrency(): string
    {
        $fromDatabase = StripeSetting::current()?->currency;

        if (filled($fromDatabase)) {
            return strtoupper((string) $fromDatabase);
        }

        return strtoupper((string) config('stripe.currency', 'MXN'));
    }

    public function resolveWebhookToleranceSeconds(): int
    {
        $fromDatabase = StripeSetting::current()?->webhook_tolerance_seconds;

        if ($fromDatabase !== null && $fromDatabase > 0) {
            return (int) $fromDatabase;
        }

        return (int) config('stripe.webhook_tolerance_seconds', 300);
    }

    public function isConfigured(): bool
    {
        $settings = StripeSetting::current();

        if ($settings && ! $settings->is_enabled) {
            return false;
        }

        return filled($this->resolveSecretKey());
    }

    /**
     * @return array<string, mixed>
     */
    public function getConnectionSummary(?StripeSetting $settings = null): array
    {
        $settings = $settings ?? StripeSetting::current();
        $secretKey = $this->resolveSecretKey();
        $webhookSecret = $this->resolveWebhookSecret();
        $managedByEnv = $settings === null || ! filled($settings->secret_key);
        $enabled = $settings?->is_enabled ?? true;

        return [
            'table_ready' => StripeSetting::tableExists(),
            'configured' => filled($secretKey),
            'enabled' => $enabled,
            'active' => $enabled && filled($secretKey),
            'managed_by_env' => $managedByEnv && filled($secretKey),
            'secret_key_masked' => $settings?->maskedSecretKey() ?? ($secretKey ? $this->maskKey($secretKey) : null),
            'publishable_key_masked' => $this->maskKey($this->resolvePublishableKey() ?? ''),
            'webhook_secret_masked' => $this->maskKey($webhookSecret ?? ''),
            'webhook_ready' => filled($webhookSecret),
            'currency' => $this->resolveCurrency(),
            'webhook_tolerance_seconds' => $this->resolveWebhookToleranceSeconds(),
            'connection_status' => $settings?->connection_status ?? (filled($secretKey) ? 'unknown' : 'misconfigured'),
            'connection_status_label' => $settings?->connectionStatusLabel() ?? (filled($secretKey) ? 'Sin verificar' : 'Incompleto'),
            'last_verified_at' => $settings?->last_verified_at,
            'last_error_message' => $settings?->last_error_message,
            'last_verification' => $settings?->last_verification ?? [],
            'webhook_url' => Route::has('webhooks.stripe') ? route('webhooks.stripe') : null,
        ];
    }

    public function verifyConnection(?StripeSetting $settings = null): bool
    {
        $settings = $settings ?? StripeSetting::currentOrCreate();

        if (! $settings->is_enabled) {
            $settings->update([
                'connection_status' => 'disabled',
                'last_error_message' => null,
                'last_verified_at' => now(),
            ]);

            return false;
        }

        $secretKey = filled($settings->secret_key)
            ? (string) $settings->secret_key
            : $this->resolveSecretKey();

        if (! filled($secretKey)) {
            $settings->update([
                'connection_status' => 'misconfigured',
                'last_error_message' => 'Falta la Secret Key de Stripe.',
                'last_verified_at' => now(),
            ]);

            return false;
        }

        try {
            $response = Http::withToken($secretKey)
                ->acceptJson()
                ->timeout(15)
                ->get('https://api.stripe.com/v1/balance');

            if (! $response->successful()) {
                $message = (string) data_get($response->json(), 'error.message', 'No se pudo validar la cuenta de Stripe.');

                $settings->update([
                    'connection_status' => 'error',
                    'last_error_message' => $message,
                    'last_verified_at' => now(),
                    'last_verification' => [
                        'http_status' => $response->status(),
                        'mode' => str_starts_with($secretKey, 'sk_live') ? 'live' : 'test',
                    ],
                ]);

                return false;
            }

            $payload = (array) $response->json();

            $settings->update([
                'connection_status' => 'connected',
                'last_error_message' => null,
                'last_verified_at' => now(),
                'last_verification' => [
                    'mode' => str_starts_with($secretKey, 'sk_live') ? 'live' : 'test',
                    'livemode' => (bool) data_get($payload, 'livemode'),
                    'default_currency' => data_get($payload, 'available.0.currency'),
                ],
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Stripe connection verification failed', [
                'error' => $e->getMessage(),
            ]);

            $settings->update([
                'connection_status' => 'error',
                'last_error_message' => $e->getMessage(),
                'last_verified_at' => now(),
            ]);

            return false;
        }
    }

    protected function maskKey(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $value = (string) $value;

        if (strlen($value) <= 12) {
            return str_repeat('•', strlen($value));
        }

        return substr($value, 0, 7).'…'.substr($value, -4);
    }
}
