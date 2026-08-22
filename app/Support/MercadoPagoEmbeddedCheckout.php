<?php

namespace App\Support;

use App\Models\TicketOrder;

/**
 * Exact, read-only authorization for Lapsique's Mercado Pago Card Brick.
 */
final class MercadoPagoEmbeddedCheckout
{
    public static function salesMode(): string
    {
        return (bool) config('mercadopago.embedded.testing', true) ? 'testing' : 'live';
    }

    public static function configurationReady(): bool
    {
        if (! (bool) config('mercadopago.embedded.enabled', false)
            || ! filled(config('mercadopago.webhook_secret'))) {
            return false;
        }

        $testing = (bool) config('mercadopago.embedded.testing', true);
        $sandbox = (bool) config('mercadopago.sandbox', false);
        $publicKey = trim((string) config('mercadopago.public_key'));
        $accessToken = trim((string) config('mercadopago.access_token'));

        if ($publicKey === '' || $accessToken === '') {
            return false;
        }

        return $testing
            ? $sandbox
                && str_starts_with($publicKey, 'TEST-')
                && str_starts_with($accessToken, 'TEST-')
            : ! $sandbox
                && ! str_starts_with($publicKey, 'TEST-')
                && ! str_starts_with($accessToken, 'TEST-');
    }

    public static function isAuthorizedEventSlug(string $slug): bool
    {
        return in_array($slug, (array) config('mercadopago.embedded.authorized_event_slugs', []), true);
    }

    public static function isEligible(TicketOrder $order): bool
    {
        if (! self::configurationReady()
            || $order->payment_provider !== 'mercadopago'
            || $order->status !== 'pending'
            || (float) $order->total <= 0) {
            return false;
        }

        $order->loadMissing(['event', 'items.product']);
        $event = $order->event;
        if (! $event || ! self::isAuthorizedEventSlug($event->slug)) {
            return false;
        }

        return $order->items->isNotEmpty()
            && $order->items->every(
                fn ($item): bool => data_get($item->product?->metadata, 'sales_mode') === self::salesMode(),
            );
    }
}
