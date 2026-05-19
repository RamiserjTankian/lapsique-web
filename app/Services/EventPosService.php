<?php

namespace App\Services;

use App\Models\AybProduct;
use App\Models\CustomerEventBalance;
use App\Models\PosCharge;
use App\Models\TicketOrder;
use App\Models\TicketAttendee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EventPosService
{
    public function getAvailableProducts(): Collection
    {
        return AybProduct::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getProduct(int $productId): AybProduct
    {
        $product = AybProduct::query()
            ->active()
            ->find($productId);

        if (! $product) {
            throw new RuntimeException('El producto AyB seleccionado no existe o esta inactivo.');
        }

        return $product;
    }

    public function chargeFromAttendee(TicketAttendee $attendee, int $productId, int $quantity, ?int $userId = null): PosCharge
    {
        if ($quantity < 1) {
            throw new RuntimeException('La cantidad debe ser al menos 1.');
        }

        $attendee->loadMissing(['customer', 'event', 'product', 'order']);

        if (! $attendee->customer_id || ! $attendee->event_id) {
            throw new RuntimeException('Este QR no esta vinculado a un cliente con saldo.');
        }

        $product = $this->getProduct($productId);
        $unitPrice = round((float) $product->price, 2);
        $total = round($unitPrice * $quantity, 2);

        return DB::transaction(function () use ($attendee, $product, $quantity, $userId, $unitPrice, $total): PosCharge {
            $balance = CustomerEventBalance::query()
                ->where('customer_id', $attendee->customer_id)
                ->where('event_id', $attendee->event_id)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                throw new RuntimeException('El cliente no tiene saldo para este evento.');
            }

            $currentBalance = round((float) $balance->balance, 2);

            if ($currentBalance < $total) {
                throw new RuntimeException('Saldo insuficiente para completar el cargo.');
            }

            $newBalance = round($currentBalance - $total, 2);

            $charge = PosCharge::create([
                'customer_event_balance_id' => $balance->id,
                'customer_id' => $attendee->customer_id,
                'event_id' => $attendee->event_id,
                'ticket_attendee_id' => $attendee->id,
                'user_id' => $userId,
                'ayb_product_id' => $product->id,
                'item_key' => $product->slug,
                'item_name' => $product->name,
                'item_type' => $product->type,
                'currency' => (string) ($product->currency ?: $balance->currency),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'metadata' => [
                    'ticket_order_id' => $attendee->ticket_order_id,
                    'ticket_product_id' => $attendee->ticket_product_id,
                    'ticket_code' => $attendee->getCheckInCode(),
                ],
            ]);

            $balance->update([
                'balance' => $newBalance,
                'total_consumed' => round(((float) $balance->total_consumed) + $total, 2),
                'metadata' => array_merge($balance->metadata ?? [], [
                    'last_pos_charge_id' => $charge->id,
                    'last_pos_charge_at' => now()->toIso8601String(),
                ]),
            ]);

            return $charge->fresh(['customer', 'event', 'attendee', 'user', 'aybProduct']);
        });
    }

    public function getBalanceForAttendee(TicketAttendee $attendee): ?CustomerEventBalance
    {
        if (! $attendee->customer_id || ! $attendee->event_id) {
            return null;
        }

        return CustomerEventBalance::query()
            ->where('customer_id', $attendee->customer_id)
            ->where('event_id', $attendee->event_id)
            ->first();
    }

    public function cancelCharge(PosCharge $charge, ?string $reason = null, ?int $userId = null): PosCharge
    {
        $reason = trim((string) $reason);

        return DB::transaction(function () use ($charge, $reason, $userId): PosCharge {
            $charge->refresh();

            if ($charge->trashed()) {
                throw new RuntimeException('Este consumo ya fue cancelado.');
            }

            $balance = CustomerEventBalance::query()
                ->whereKey($charge->customer_event_balance_id)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                throw new RuntimeException('No encontramos el saldo asociado a este consumo.');
            }

            $metadata = $charge->metadata ?? [];
            $metadata['cancelled_at'] = now()->toIso8601String();
            $metadata['cancelled_by_user_id'] = $userId;
            $metadata['cancel_reason'] = $reason !== '' ? $reason : 'cancelled_from_balance_history';

            $charge->update(['metadata' => $metadata]);
            $charge->delete();

            $this->recalculateBalanceTimeline($balance);

            return $charge;
        });
    }

    protected function recalculateBalanceTimeline(CustomerEventBalance $balance): void
    {
        $credits = TicketOrder::query()
            ->where('customer_id', $balance->customer_id)
            ->where('event_id', $balance->event_id)
            ->where('status', 'paid')
            ->get()
            ->map(function (TicketOrder $order): array {
                $amount = round((float) (data_get($order->metadata, 'customer_balance_amount') ?? $order->subtotal ?? 0), 2);
                $appliedAt = data_get($order->metadata, 'customer_balance_applied_at');
                $occurredAt = $appliedAt ? Carbon::parse($appliedAt) : ($order->paid_at ?? $order->created_at ?? now());

                return [
                    'type' => 'credit',
                    'id' => $order->id,
                    'at' => $occurredAt,
                    'amount' => $amount,
                ];
            })
            ->filter(fn (array $entry): bool => $entry['amount'] > 0);

        $charges = PosCharge::query()
            ->where('customer_event_balance_id', $balance->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $timeline = $credits
            ->merge($charges->map(fn (PosCharge $charge): array => [
                'type' => 'charge',
                'id' => $charge->id,
                'at' => $charge->created_at ?? now(),
                'amount' => round((float) $charge->total, 2),
            ]))
            ->sort(function (array $left, array $right): int {
                $timeCompare = $left['at']->getTimestamp() <=> $right['at']->getTimestamp();

                if ($timeCompare !== 0) {
                    return $timeCompare;
                }

                if ($left['type'] !== $right['type']) {
                    return $left['type'] === 'credit' ? -1 : 1;
                }

                return $left['id'] <=> $right['id'];
            })
            ->values();

        $runningBalance = 0.0;
        $chargeIds = [];

        foreach ($timeline as $entry) {
            if ($entry['type'] === 'credit') {
                $runningBalance = round($runningBalance + $entry['amount'], 2);

                continue;
            }

            $charge = $charges->firstWhere('id', $entry['id']);

            if (! $charge) {
                continue;
            }

            $before = round($runningBalance, 2);
            $after = round($before - $entry['amount'], 2);

            $charge->update([
                'balance_before' => $before,
                'balance_after' => $after,
            ]);

            $runningBalance = $after;
            $chargeIds[] = $charge->id;
        }

        $lastChargeId = empty($chargeIds) ? null : end($chargeIds);

        $balance->update([
            'balance' => round($runningBalance, 2),
            'total_consumed' => round((float) $charges->sum('total'), 2),
            'metadata' => array_merge($balance->metadata ?? [], [
                'last_pos_charge_id' => $lastChargeId,
                'last_pos_charge_at' => $lastChargeId
                    ? optional($charges->firstWhere('id', $lastChargeId)?->created_at)->toIso8601String()
                    : null,
            ]),
        ]);
    }
}
