<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerEventBalance;
use App\Models\Event;
use App\Models\TicketAttendee;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketProduct;
use App\Services\EventPosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventPosServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_charges_house_cocktail_from_customer_balance(): void
    {
        $event = Event::create([
            'title' => 'POS Test',
            'slug' => Str::slug('POS Test'),
        ]);

        $customer = Customer::create([
            'name' => 'Cliente POS',
            'email' => 'pos@example.com',
            'status' => 'customer',
            'source' => 'ticketing',
        ]);

        $order = TicketOrder::create([
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'paid',
            'payment_provider' => 'mercadopago',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 150,
            'total' => 1150,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'attendees_registered' => 1,
            'buyer_name' => 'Cliente POS',
            'buyer_email' => 'pos@example.com',
        ]);

        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'Acceso Consumo',
            'category' => 'ticket',
            'currency' => 'MXN',
            'price' => 1150,
            'service_charge_pct' => 15,
            'access_units' => 1,
            'check_in_limit' => 1,
            'is_active' => true,
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
        ]);

        $attendee = TicketAttendee::create([
            'ticket_order_id' => $order->id,
            'ticket_order_item_id' => $item->id,
            'ticket_product_id' => $product->id,
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'registered',
            'name' => 'Cliente POS',
            'email' => 'pos@example.com',
            'whatsapp' => '9991112233',
            'check_in_limit' => 1,
            'check_in_count' => 0,
        ]);

        $balance = CustomerEventBalance::create([
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'last_ticket_order_id' => $order->id,
            'currency' => 'MXN',
            'balance' => 1000,
            'total_credited' => 1000,
            'total_consumed' => 0,
        ]);

        $charge = app(EventPosService::class)->chargeFromAttendee($attendee, 'paloma_marina', 2);

        $balance->refresh();

        $this->assertSame('500.00', number_format((float) $charge->total, 2, '.', ''));
        $this->assertSame('500.00', number_format((float) $balance->balance, 2, '.', ''));
        $this->assertSame('500.00', number_format((float) $balance->total_consumed, 2, '.', ''));
        $this->assertDatabaseHas('pos_charges', [
            'id' => $charge->id,
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'quantity' => 2,
        ]);
    }
}
