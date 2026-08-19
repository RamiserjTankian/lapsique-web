<?php

namespace App\Support;

use App\Models\TicketOrder;

final class PaymentAmount
{
    public static function assertMercadoPago(TicketOrder $order, mixed $amount, mixed $currency): void
    {
        $expectedCurrency = strtoupper(trim((string) $order->currency));
        $receivedCurrency = strtoupper(trim((string) $currency));

        if (preg_match('/^[A-Z]{3}$/D', $receivedCurrency) !== 1
            || $receivedCurrency !== $expectedCurrency
            || ! is_numeric($amount)
            || (int) round(((float) $amount) * 100) !== (int) round(((float) $order->total) * 100)) {
            throw new PaymentAmountMismatchException('El importe o la moneda del pago no coincide con la orden.');
        }
    }
}
