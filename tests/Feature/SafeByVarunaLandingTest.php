<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SafeByVarunaLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_sales_landing_inherits_the_lapsique_layout_without_a_nested_viewport_microsite(): void
    {
        $landing = file_get_contents(resource_path('js/components/lapsique/SafeByVarunaLanding.tsx'));
        $embeddedPayment = file_get_contents(resource_path('js/mercadopago-embedded.js'));
        $home = file_get_contents(resource_path('js/pages/Home.tsx'));

        $this->assertStringNotContainsString('<main id="main-content">', $landing);
        $this->assertStringNotContainsString('left-1/2 w-screen -translate-x-1/2', $landing);
        $this->assertStringNotContainsString('CHECKOUT DE PRUEBA', $landing);
        $this->assertStringNotContainsString('Fase única · Testing', $landing);
        $this->assertStringContainsString('Compra tu boleto.', $landing);
        $this->assertStringContainsString('Haz tu prerregistro.', $landing);
        $this->assertStringContainsString("route('leads.capture'", $landing);
        $this->assertStringContainsString('safe-by-varuna-preregistration', $landing);
        $this->assertStringContainsString('Prerregistro gratuito · no requiere pago', $landing);
        $this->assertStringContainsString('Solo te inscribe para recibir el aviso de apertura de venta.', $landing);
        $this->assertStringContainsString("Accept: 'application/json'", $landing);
        $this->assertStringContainsString('data-mercadopago-configuration-url', $landing);
        $this->assertStringContainsString('safe-mercadopago-card-form', $landing);
        $this->assertStringContainsString('mercadopago:payment-submitted', $landing);
        $this->assertStringNotContainsString("public_key?.startsWith('TEST-')", $embeddedPayment);
        $this->assertStringContainsString('Lapsique nunca recibe ni almacena', $landing);
        $this->assertStringNotContainsString('Prerregistro · $105 MXN', $landing);
        $this->assertStringContainsString('VenueGallery', $landing);
        $this->assertStringContainsString('KapiSetCarousel', $landing);
        $this->assertStringContainsString('bz6WRoPlRAc', $landing);
        $this->assertStringContainsString('Zvfnp5f0avs', $landing);
        $this->assertStringNotContainsString('Cupo limitado · sin reembolsos', $landing);
        $this->assertStringContainsString('casa-luma-preview.mp4', $landing);
        $this->assertStringContainsString("prefers-reduced-motion: reduce", $landing);
        $this->assertStringContainsString('Pausar video', $landing);
        $this->assertStringContainsString('<FeaturedSafeEvent events={sceneEvents} />', $home);
        $this->assertStringContainsString('safe-by-varuna-1-edition', $home);
    }

    public function test_lapsique_event_page_exposes_the_single_testing_catalog_and_shared_view_event_id(): void
    {
        config([
            'meta.capi.enabled' => true,
            'meta.pixel.id' => 'pixel-safe',
            'meta.marketing_api.access_token' => 'meta-test-token',
            'meta.marketing_api.api_version' => 'v21.0',
            'mercadopago.embedded.enabled' => false,
        ]);
        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
        ]);

        $this->artisan('events:register-safe-by-varuna-draft', [
            '--activate-testing' => true,
            '--confirm' => 'ACTIVATE_TESTING',
        ])->assertSuccessful();
        $event = Event::where('slug', 'safe-by-varuna-1-edition')->firstOrFail();

        $this->withCookie('_fbp', 'fb.1.123.456')
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Events/Show')
                ->where('event.slug', 'safe-by-varuna-1-edition')
                ->where('event.event_timezone', 'America/Mexico_City')
                ->where('event.has_tickets', true)
                ->has('event.ticket_products', 1)
                ->where('event.ticket_products.0.base_price', 100)
                ->where('event.ticket_products.0.service_charge_amount', 5)
                ->where('event.ticket_products.0.total', 105)
                ->where('event.ticket_products.0.available', 350)
                ->where('event.ticket_products.0.sales_mode', 'testing')
                ->where('event.ticket_products.0.embedded_checkout_ready', false)
                ->where('event.ticket_products.0.max_per_order', 6)
                ->where('viewContentEventId', fn ($value) => is_string($value) && str_starts_with($value, 'event_view_'.$event->id.'_'))
            );

        Http::assertSent(function ($request) use ($event) {
            return data_get($request->data(), 'data.0.event_name') === 'ViewContent'
                && str_starts_with((string) data_get($request->data(), 'data.0.event_id'), 'event_view_'.$event->id.'_')
                && data_get($request->data(), 'data.0.custom_data.value') === 105.0
                && data_get($request->data(), 'data.0.user_data.fbp') === 'fb.1.123.456';
        });
    }
}
