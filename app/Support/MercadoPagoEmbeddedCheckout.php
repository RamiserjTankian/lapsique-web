<?php

namespace App\Support;

use App\Models\TicketOrder;

/**
 * Exact, read-only authorization for Lapsique's Mercado Pago Card Brick.
 */
final class MercadoPagoEmbeddedCheckout
{
    public static function configurationReady(): bool
    {
        return (bool) config('mercadopago.embedded.enabled', false)
            && (bool) config('mercadopago.embedded.testing', true)
            && (bool) config('mercadopago.sandbox', false)
            && str_starts_with(trim((string) config('mercadopago.public_key')), 'TEST-')
            && str_starts_with(trim((string) config('mercadopago.access_token')), 'TEST-')
            && filled(config('mercadopago.webhook_secret'));
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
                fn ($item): bool => data_get($item->product?->metadata, 'sales_mode') === 'testing',
            );
    }
}
