<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailtrapWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailtrap_webhook_accepts_valid_signature(): void
    {
        config()->set('services.mailtrap.webhook_secret', 'test-secret');

        $payload = json_encode([
            [
                'type' => 'delivered',
                'email' => 'buyer@example.com',
                'message_id' => 'msg_123',
            ],
        ], JSON_THROW_ON_ERROR);

        $signature = hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->call(
            'POST',
            route('webhooks.mailtrap.events'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_MAILTRAP_SIGNATURE' => $signature,
            ],
            $payload,
        );

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_mailtrap_webhook_rejects_invalid_signature(): void
    {
        config()->set('services.mailtrap.webhook_secret', 'test-secret');

        $payload = json_encode([
            [
                'type' => 'opened',
                'email' => 'buyer@example.com',
                'message_id' => 'msg_123',
            ],
        ], JSON_THROW_ON_ERROR);

        $response = $this->call(
            'POST',
            route('webhooks.mailtrap.events'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_MAILTRAP_SIGNATURE' => 'invalid-signature',
            ],
            $payload,
        );

        $response->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Invalid signature',
            ]);
    }
}
