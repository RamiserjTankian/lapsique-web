<?php

namespace Tests\Feature;

use App\Jobs\SendTicketAccessEmailJob;
use App\Mail\TicketOrderConfirmationEmail;
use App\Models\Event;
use App\Models\TicketProduct;
use App\Services\TicketPassPdfService;
use App\Services\TicketOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicketCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_service_splits_consumable_and_service_fee(): void
    {
        $event = Event::create([
            'title' => 'Zal Marina Launch',
            'slug' => 'zal-marina-launch',
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

        $order = app(TicketOrderService::class)->createOrder(
            $event,
            [$product->id => 1],
            [
                'name' => 'Buyer Test',
                'email' => 'buyer@example.com',
                'whatsapp' => '9991112233',
                'phone' => '9991112233',
            ]
        );

        $this->assertSame('1000.00', $order->subtotal);
        $this->assertSame('150.00', $order->fee);
        $this->assertSame('1150.00', $order->total);
        $this->assertSame(1, $order->attendees_expected);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame(150.0, (float) data_get($order->items()->first()->metadata, 'line_fee'));
        $this->assertSame('prospect', $order->customer->status);
    }

    public function test_attendee_registration_can_be_saved_partially_for_table_orders(): void
    {
        Queue::fake();

        $event = Event::create([
            'title' => 'Mesa Test',
            'slug' => Str::slug('Mesa Test'),
        ]);

        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'Mesa Frente al DJ',
            'category' => 'table',
            'currency' => 'MXN',
            'price' => 8280,
            'service_charge_pct' => 15,
            'access_units' => 6,
            'check_in_limit' => 1,
            'stock' => 10,
            'is_active' => true,
        ]);

        $service = app(TicketOrderService::class);
        $order = $service->createOrder(
            $event,
            [$product->id => 1],
            [
                'name' => 'Mesa Buyer',
                'email' => 'mesa@example.com',
                'whatsapp' => '9991112233',
                'phone' => '9991112233',
            ]
        );

        $order->markAsPaid();
        $service->ensureAttendees($order);
        $order->refresh();

        $firstAttendee = $order->attendees()->firstOrFail();

        $response = $this->post(route('tickets.attendees.store', $order), [
            'attendees' => [
                $firstAttendee->id => [
                    'name' => 'Invitado Uno',
                    'email' => 'invitado1@example.com',
                    'whatsapp' => '9990001111',
                    'instagram_handle' => '@invitado1',
                ],
            ],
        ]);

        $response->assertRedirect(route('tickets.success', $order));

        $order->refresh();

        $this->assertSame(6, $order->attendees_expected);
        $this->assertSame(1, $order->attendees_registered);
        $this->assertDatabaseHas('ticket_attendees', [
            'id' => $firstAttendee->id,
            'status' => 'registered',
            'email' => 'invitado1@example.com',
        ]);
        $this->assertSame(5, $order->attendees()->where('status', 'pending')->count());

        Queue::assertPushed(SendTicketAccessEmailJob::class, 1);
    }

    public function test_paid_table_order_confirmation_email_mentions_attached_individual_qrs_and_attaches_pdf(): void
    {
        Queue::fake();

        $event = Event::create([
            'title' => 'Mesa Confirmada Test',
            'slug' => Str::slug('Mesa Confirmada Test'),
        ]);

        $product = TicketProduct::create([
            'event_id' => $event->id,
            'name' => 'Mesa Frente al DJ',
            'category' => 'table',
            'currency' => 'MXN',
            'price' => 8280,
            'service_charge_pct' => 15,
            'access_units' => 6,
            'check_in_limit' => 1,
            'stock' => 10,
            'is_active' => true,
        ]);

        $service = app(TicketOrderService::class);
        $order = $service->createOrder(
            $event,
            [$product->id => 1],
            [
                'name' => 'Mesa Buyer',
                'email' => 'mesa-confirmada@example.com',
                'whatsapp' => '9991112233',
                'phone' => '9991112233',
            ]
        );

        $service->syncStripePaymentIntent($order, [
            'id' => 'pi_table_paid_test',
            'status' => 'succeeded',
            'payment_method' => 'pm_table_paid_test',
        ]);

        $order = $order->fresh(['event', 'items', 'attendees']);

        $this->assertSame('paid', $order->status);
        $this->assertCount(6, $order->attendees);
        $mailable = new TicketOrderConfirmationEmail($order, 'tracking-token-test');
        $pdfService = app(TicketPassPdfService::class);
        $expectedFilename = $pdfService->filenameForEvent($event);

        $mailable
            ->assertHasSubject("✅ Compra confirmada: {$event->title}")
            ->assertSeeInHtml('Los accesos se encuentran dentro del PDF adjunto y cada uno incluye un QR individual.')
            ->assertSeeInHtml('Guarda el PDF adjunto: ahí encontrarás los QR individuales de la mesa y podrás compartirlos con tus invitados.');

        $reflection = new \ReflectionClass($mailable);
        $rawAttachments = $reflection->getProperty('rawAttachments');
        $rawAttachments->setAccessible(true);
        $attachments = $rawAttachments->getValue($mailable);

        $this->assertCount(1, $attachments);
        $this->assertSame($expectedFilename, $attachments[0]['name']);
        $this->assertSame('application/pdf', $attachments[0]['options']['mime'] ?? null);
        $this->assertNotEmpty($attachments[0]['data']);
        $this->assertStringStartsWith('%PDF-', $attachments[0]['data']);
    }

    public function test_paid_order_promotes_customer_and_credits_event_balance(): void
    {
        Queue::fake();

        $event = Event::create([
            'title' => 'Credito Test',
            'slug' => Str::slug('Credito Test'),
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
            'stock' => 100,
            'is_active' => true,
        ]);

        $service = app(TicketOrderService::class);
        $order = $service->createOrder(
            $event,
            [$product->id => 1],
            [
                'name' => 'Buyer Credit',
                'email' => 'credit@example.com',
                'whatsapp' => '9991112233',
                'phone' => '9991112233',
            ]
        );

        $service->syncStripePaymentIntent($order, [
            'id' => 'pi_test_123',
            'status' => 'succeeded',
            'payment_method' => 'pm_test_123',
        ]);

        $order->refresh();
        $order->customer->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertSame('customer', $order->customer->status);
        $this->assertDatabaseHas('customer_event_balances', [
            'customer_id' => $order->customer_id,
            'event_id' => $event->id,
            'currency' => 'MXN',
        ]);
        $this->assertSame(
            '1000.00',
            number_format((float) $order->customer->eventBalances()->where('event_id', $event->id)->value('balance'), 2, '.', '')
        );
    }
}
