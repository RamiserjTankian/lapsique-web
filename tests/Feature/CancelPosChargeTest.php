<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerEventBalance;
use App\Models\Event;
use App\Models\PosCharge;
use App\Models\TicketOrder;
use App\Services\EventPosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelPosChargeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancel_pos_charge_returns_balance_and_recalculates_following_consumptions(): void
    {
        $customer = Customer::create([
            'name' => 'Andres Sanchez',
            'email' => 'andres@test.com',
            'status' => 'customer',
        ]);

        $event = Event::create([
            'title' => 'REBOLLEDO',
            'slug' => 'rebolledo-test-pos',
            'starts_at' => now()->addWeek(),
        ]);

        $balance = CustomerEventBalance::create([
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'currency' => 'MXN',
            'balance' => 1250,
            'total_credited' => 2000,
            'total_consumed' => 750,
            'metadata' => [],
        ]);

        TicketOrder::create([
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'paid',
            'payment_provider' => 'stripe',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 0,
            'total' => 1000,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'attendees_registered' => 1,
            'buyer_name' => 'Andres',
            'buyer_email' => 'andres@test.com',
            'paid_at' => now()->subHours(4),
            'metadata' => [
                'customer_balance_applied_at' => now()->subHours(4)->toIso8601String(),
                'customer_balance_amount' => 1000,
            ],
        ]);

        TicketOrder::create([
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'paid',
            'payment_provider' => 'stripe',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 0,
            'total' => 1000,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'attendees_registered' => 1,
            'buyer_name' => 'Andres',
            'buyer_email' => 'andres@test.com',
            'paid_at' => now()->subHours(2),
            'metadata' => [
                'customer_balance_applied_at' => now()->subHours(2)->toIso8601String(),
                'customer_balance_amount' => 1000,
            ],
        ]);

        $firstCharge = PosCharge::create([
            'customer_event_balance_id' => $balance->id,
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'item_key' => 'paloma-marina',
            'item_name' => 'Paloma Marina',
            'item_type' => 'beverage',
            'currency' => 'MXN',
            'quantity' => 1,
            'unit_price' => 250,
            'total' => 250,
            'balance_before' => 1000,
            'balance_after' => 750,
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        $secondCharge = PosCharge::create([
            'customer_event_balance_id' => $balance->id,
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'item_key' => 'botella-vino',
            'item_name' => 'Botella de vino',
            'item_type' => 'beverage',
            'currency' => 'MXN',
            'quantity' => 1,
            'unit_price' => 500,
            'total' => 500,
            'balance_before' => 1750,
            'balance_after' => 1250,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        app(EventPosService::class)->cancelCharge($firstCharge, 'consumo de prueba', 1);

        $balance->refresh();
        $secondCharge->refresh();

        $this->assertSoftDeleted('pos_charges', ['id' => $firstCharge->id]);
        $this->assertSame('1500.00', (string) $balance->balance);
        $this->assertSame('500.00', (string) $balance->total_consumed);
        $this->assertSame('2000.00', (string) $secondCharge->balance_before);
        $this->assertSame('1500.00', (string) $secondCharge->balance_after);
    }
}
