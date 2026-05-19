<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerEventBalance;
use App\Models\Event;
use App\Models\TicketAttendee;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketProduct;
use App\Services\TicketOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelTicketOrderFromAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_order_can_be_cancelled_from_admin_and_reverts_event_sale_effects(): void
    {
        $event = Event::create([
            'title' => 'Venta de prueba',
            'slug' => 'venta-prueba',
            'starts_at' => now()->addWeek(),
        ]);

        $customer = Customer::create([
            'name' => 'Cliente Test',
            'email' => 'cliente@test.com',
            'status' => 'customer',
        ]);

        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'Acceso General',
            'category' => 'ticket',
            'currency' => 'MXN',
            'price' => 1150,
            'service_charge_pct' => 15,
            'access_units' => 1,
            'check_in_limit' => 1,
            'stock' => 50,
            'sold_count' => 1,
            'reserved_count' => 0,
            'is_active' => true,
        ]);

        $order = TicketOrder::create([
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'paid',
            'payment_provider' => 'stripe',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 150,
            'total' => 1150,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'attendees_registered' => 1,
            'buyer_name' => 'Cliente Test',
            'buyer_email' => 'cliente@test.com',
            'buyer_phone' => '9990001111',
            'buyer_whatsapp' => '9990001111',
            'paid_at' => now(),
            'metadata' => [
                'reservation_status' => 'committed',
                'customer_balance_applied_at' => now()->toIso8601String(),
                'customer_balance_amount' => 1000,
            ],
        ]);

        $item = TicketOrderItem::create([
            'ticket_order_id' => $order->id,
            'ticket_product_id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'quantity' => 1,
            'unit_price' => 1150,
            'total_price' => 1150,
            'access_units' => 1,
            'check_in_limit' => 1,
            'metadata' => [],
        ]);

        TicketAttendee::create([
            'ticket_order_id' => $order->id,
            'ticket_order_item_id' => $item->id,
            'ticket_product_id' => $product->id,
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'registered',
            'name' => 'Cliente Test',
            'email' => 'cliente@test.com',
            'registered_at' => now(),
        ]);

        CustomerEventBalance::create([
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'last_ticket_order_id' => $order->id,
            'currency' => 'MXN',
            'balance' => 1000,
            'total_credited' => 1000,
            'total_consumed' => 0,
            'metadata' => [],
        ]);

        app(TicketOrderService::class)->cancelOrderFromAdmin($order, 'venta de prueba', 1);

        $order->refresh();
        $product->refresh();
        $balance = CustomerEventBalance::first();
        $attendee = TicketAttendee::first();

        $this->assertSame('cancelled', $order->status);
        $this->assertSame(0, $order->attendees_registered);
        $this->assertSame(0, $product->sold_count);
        $this->assertSame('cancelled', $attendee->status);
        $this->assertSame('0.00', (string) $balance->balance);
        $this->assertNull($balance->fresh()->last_ticket_order_id);
    }
}
