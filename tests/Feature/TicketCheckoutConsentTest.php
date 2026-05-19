<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketCheckoutConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_terms_page_is_available(): void
    {
        $response = $this->get(route('legal.terms'));

        $response->assertOk();
        $response->assertSee('Términos y condiciones');
        $response->assertSee('Los accesos son personales, intransferibles y no reembolsables.');
    }

    public function test_checkout_requires_accepting_terms(): void
    {
        $event = Event::create([
            'title' => 'Consent Test',
            'slug' => 'consent-test',
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

        $response = $this->from(route('events.show', $event) . '#tickets')->post(route('tickets.checkout.store', $event), [
            'buyer_name' => 'Buyer Test',
            'buyer_email' => 'buyer@example.com',
            'buyer_whatsapp' => '9991112233',
            'items' => [
                $product->id => 1,
            ],
            'payment_provider' => 'mercadopago',
        ]);

        $response->assertRedirect(route('events.show', $event) . '#tickets');
        $response->assertSessionHasErrors('consent_terms');
    }
}
