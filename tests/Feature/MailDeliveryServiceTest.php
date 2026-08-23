<?php

namespace Tests\Feature;

use App\Mail\TicketAccessEmail;
use App\Models\Event;
use App\Models\TicketAttendee;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketProduct;
use App\Services\MailDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class MailDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailtrap_api_payload_includes_pdf_attachment_for_ticket_access_email(): void
    {
        config()->set('services.mailtrap.api_token', 'test-mailtrap-token');
        config()->set('services.mailtrap.api_endpoint', 'https://send.api.mailtrap.io/api/send');
        config()->set('mail.from.address', 'noreply@zalmarina.party');
        config()->set('mail.from.name', 'ZAL MARINA');

        $event = Event::create([
            'title' => 'REBOLLEDO at Zal Marina',
            'slug' => 'rebolledo-mailtrap-attachment-test',
            'starts_at' => now()->addWeek(),
            'venue' => 'Zal Marina',
            'city' => 'Progreso, Yucatán',
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
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = TicketOrder::create([
            'event_id' => $event->id,
            'status' => 'paid',
            'payment_provider' => 'stripe',
            'currency' => 'MXN',
            'subtotal' => 1000,
            'fee' => 150,
            'total' => 1150,
            'items_quantity' => 1,
            'attendees_expected' => 1,
            'attendees_registered' => 1,
            'buyer_name' => 'Ramiro Diaz Ramos',
            'buyer_email' => 'ramiro@bluepointrs.com',
            'buyer_phone' => '7444372758',
            'buyer_whatsapp' => '7444372758',
            'metadata' => ['reservation_status' => 'committed'],
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
            'metadata' => [
                'unit_base_price' => 1000,
                'unit_fee' => 150,
                'line_subtotal' => 1000,
                'line_fee' => 150,
            ],
        ]);

        $attendee = TicketAttendee::create([
            'ticket_order_id' => $order->id,
            'ticket_order_item_id' => $item->id,
            'ticket_product_id' => $product->id,
            'event_id' => $event->id,
            'status' => 'registered',
            'name' => 'Ramiro Diaz Ramos',
            'email' => 'ramiro@bluepointrs.com',
            'whatsapp' => '7444372758',
            'phone' => '7444372758',
            'registered_at' => now(),
        ]);

        Http::fake([
            'https://send.api.mailtrap.io/api/send' => Http::response([
                'message_id' => Str::uuid()->toString(),
            ], 200),
        ]);

        $messageId = app(MailDeliveryService::class)->send(
            new TicketAccessEmail($attendee->load('event', 'product', 'order'), 'tracking-token-mailtrap-test'),
            'ramiro@bluepointrs.com',
            'Ramiro Diaz Ramos',
            'ticket-access'
        );

        $this->assertNotNull($messageId);

        Http::assertSent(function ($request) use ($attendee) {
            $payload = $request->data();
            $attachments = $payload['attachments'] ?? [];

            $this->assertCount(1, $attachments);
            $this->assertSame('pase-' . $attendee->id . '.pdf', $attachments[0]['filename'] ?? null);
            $this->assertSame('application/pdf', $attachments[0]['type'] ?? null);
            $this->assertSame('attachment', $attachments[0]['disposition'] ?? null);
            $this->assertNotEmpty($attachments[0]['content'] ?? null);
            $this->assertStringStartsWith('%PDF-', base64_decode($attachments[0]['content'], true) ?: '');

            return $request->url() === 'https://send.api.mailtrap.io/api/send';
        });
    }
}
