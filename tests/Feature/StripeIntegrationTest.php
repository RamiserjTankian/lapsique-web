<?php

namespace Tests\Feature;

use App\Models\StripeSetting;
use App\Services\StripeIntegrationService;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class StripeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_secret_from_database_over_env(): void
    {
        config([
            'stripe.secret_key' => 'sk_test_from_env',
        ]);

        StripeSetting::query()->create([
            'is_enabled' => true,
            'secret_key' => 'sk_test_from_db',
            'currency' => 'MXN',
            'webhook_tolerance_seconds' => 300,
        ]);

        $integration = app(StripeIntegrationService::class);

        $this->assertSame('sk_test_from_db', $integration->resolveSecretKey());
        $this->assertTrue($integration->isConfigured());
    }

    public function test_is_not_configured_when_disabled_in_database(): void
    {
        config([
            'stripe.secret_key' => 'sk_test_from_env',
        ]);

        StripeSetting::query()->create([
            'is_enabled' => false,
            'secret_key' => 'sk_test_from_db',
            'currency' => 'MXN',
            'webhook_tolerance_seconds' => 300,
        ]);

        $this->assertFalse(app(StripeIntegrationService::class)->isConfigured());
    }

    public function test_verify_connection_marks_record_as_connected(): void
    {
        Http::fake([
            'api.stripe.com/v1/balance' => Http::response([
                'livemode' => false,
                'available' => [
                    ['currency' => 'mxn', 'amount' => 1000],
                ],
            ], 200),
        ]);

        $settings = StripeSetting::query()->create([
            'is_enabled' => true,
            'secret_key' => 'sk_test_1234567890',
            'currency' => 'MXN',
            'webhook_tolerance_seconds' => 300,
        ]);

        $ok = app(StripeIntegrationService::class)->verifyConnection($settings);

        $this->assertTrue($ok);
        $settings->refresh();
        $this->assertSame('connected', $settings->connection_status);
        $this->assertNull($settings->last_error_message);
        $this->assertNotNull($settings->last_verified_at);
        $this->assertSame('test', $settings->last_verification['mode'] ?? null);
    }

    public function test_verify_connection_records_error_on_invalid_key(): void
    {
        Http::fake([
            'api.stripe.com/v1/balance' => Http::response([
                'error' => [
                    'message' => 'Invalid API Key provided',
                ],
            ], 401),
        ]);

        $settings = StripeSetting::query()->create([
            'is_enabled' => true,
            'secret_key' => 'sk_test_invalid',
            'currency' => 'MXN',
            'webhook_tolerance_seconds' => 300,
        ]);

        $ok = app(StripeIntegrationService::class)->verifyConnection($settings);

        $this->assertFalse($ok);
        $settings->refresh();
        $this->assertSame('error', $settings->connection_status);
        $this->assertStringContainsString('Invalid API Key', (string) $settings->last_error_message);
    }

    public function test_stripe_service_uses_database_secret_key(): void
    {
        StripeSetting::query()->create([
            'is_enabled' => true,
            'secret_key' => 'sk_test_db_only',
            'currency' => 'MXN',
            'webhook_tolerance_seconds' => 300,
        ]);

        config(['stripe.secret_key' => '']);

        $service = app(StripeService::class);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('requireSecretKey');
        $method->setAccessible(true);

        $this->assertSame('sk_test_db_only', $method->invoke($service));
    }

    public function test_stripe_service_throws_when_not_configured(): void
    {
        config([
            'stripe.secret_key' => '',
        ]);

        $service = app(StripeService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stripe no configurado');

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('requireSecretKey');
        $method->setAccessible(true);
        $method->invoke($service);
    }
}
