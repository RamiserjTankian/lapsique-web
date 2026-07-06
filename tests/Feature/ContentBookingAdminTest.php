<?php

namespace Tests\Feature;

use App\Filament\Pages\AdminDashboard;
use App\Filament\Pages\ContentBookingSalesDashboard;
use App\Filament\Pages\SalesHub;
use App\Filament\Resources\BookingSlots\BookingSlotResource;
use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Filament\Resources\ContentBookings\Pages\CreateContentBooking;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Widgets\ContentBookingMenuServicesOverviewWidget;
use App\Filament\Widgets\ContentBookingPipelineTableWidget;
use App\Filament\Widgets\ContentBookingSalesOrdersTableWidget;
use App\Filament\Widgets\ContentBookingSalesOverviewWidget;
use App\Filament\Widgets\LeadManagementStatsWidget;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\BookingSlot;
use App\Models\ContactLog;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentBookingAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_admin_resources_and_sales_dashboard_render(): void
    {
        $user = User::factory()->create();
        $slot = BookingSlot::create([
            'date' => now()->addDays(2)->toDateString(),
            'time_label' => '2:00 PM',
            'time_value' => '14:00',
            'max_bookings' => 3,
            'booked_count' => 3,
            'is_active' => true,
        ]);

        $confirmed = $this->createBooking($slot, [
            'client_name' => 'Cliente Confirmado',
            'client_email' => 'confirmado@example.com',
            'service_type' => ContentBooking::SERVICE_DJ_SET,
            'amount' => 4000,
            'status' => 'confirmed',
            'paid_at' => now(),
            'payment_provider' => 'stripe',
            'stripe_checkout_session_id' => 'cs_admin_confirmed',
            'stripe_payment_intent_id' => 'pi_admin_confirmed',
            'stripe_status' => 'succeeded',
            'utm_source' => 'meta',
        ]);

        $this->createBooking($slot, [
            'client_name' => 'Cliente Pendiente',
            'client_email' => 'pendiente@example.com',
            'service_type' => ContentBooking::SERVICE_DRONE_SESSION,
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
        ]);

        $this->createBooking($slot, [
            'client_name' => 'Cliente Fallido',
            'client_email' => 'fallido@example.com',
            'status' => 'failed',
            'payment_provider' => 'stripe',
        ]);

        $lead = Customer::create([
            'name' => 'Lead Operativo',
            'email' => 'lead@example.com',
            'phone' => '529841111111',
            'whatsapp' => '529841111111',
            'status' => 'lead',
            'source' => 'food_reels',
            'lifecycle_stage' => 'sql',
            'lead_score' => 80,
            'subscribed_whatsapp' => true,
            'metadata' => [
                'follow_up_status' => 'pending_follow_up',
            ],
            'last_interaction_at' => now(),
        ]);

        ContactLog::create([
            'customer_id' => $lead->id,
            'channel' => 'whatsapp',
            'type' => 'followup',
            'subject' => 'Seguimiento lead comida',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(ContentBookingResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Cliente Confirmado')
            ->assertSee('Stripe');

        $this->actingAs($user)
            ->get(ContentBookingResource::getUrl('create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(ContentBookingResource::getUrl('view', ['record' => $confirmed]))
            ->assertOk()
            ->assertSee('Cliente Confirmado')
            ->assertSee('cs_admin_confirmed');

        $this->actingAs($user)
            ->get(BookingSlotResource::getUrl('index'))
            ->assertOk()
            ->assertSee('2:00 PM');

        $this->actingAs($user)
            ->get(CustomerResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Lead Operativo')
            ->assertSee('food_reels');

        $this->actingAs($user)
            ->get(CustomerResource::getUrl('view', ['record' => $lead]))
            ->assertOk()
            ->assertSee('Lead Operativo')
            ->assertSee('Valor y actividad')
            ->assertSee('Atribución');

        $this->actingAs($user)
            ->get(CustomerResource::getUrl('edit', ['record' => $lead]))
            ->assertOk()
            ->assertSee('Lead score')
            ->assertSee('Acepta WhatsApp');

        $this->actingAs($user)
            ->get(AdminDashboard::getUrl())
            ->assertOk()
            ->assertSee('Nueva reserva')
            ->assertSee('Leads / clientes')
            ->assertSee('Contactos')
            ->assertSee('Landing analytics');

        $this->actingAs($user)
            ->get(SalesHub::getUrl())
            ->assertOk()
            ->assertSee('Ventas')
            ->assertSee('Ver reservas de sesiones')
            ->assertSee('Ver clientes de sesiones')
            ->assertSee('Ver leads')
            ->assertSee('Ver contactos')
            ->assertSee('Ver landing analytics');

        $this->actingAs($user)
            ->get(ContentBookingSalesDashboard::getUrl())
            ->assertOk()
            ->assertSee('Ventas de sesiones de contenido');

        Livewire::test(ContentBookingSalesOverviewWidget::class)
            ->assertSee('Ingresos sesiones')
            ->assertSee('$4,000 MXN');

        Livewire::test(ContentBookingSalesOrdersTableWidget::class)
            ->assertSee('Ventas de sesiones confirmadas')
            ->assertSee('Cliente Confirmado');

        Livewire::test(ContentBookingMenuServicesOverviewWidget::class)
            ->assertSee('DJ Sets')
            ->assertSee('Vuelos con dron')
            ->assertSee('Avances de obra')
            ->assertSee('Comida');

        Livewire::test(ContentBookingPipelineTableWidget::class)
            ->assertSee('Pipeline de sesiones por landing')
            ->assertSee('Cliente Confirmado')
            ->assertSee('Cliente Pendiente');

        Livewire::test(LeadManagementStatsWidget::class)
            ->assertSee('Leads nuevos')
            ->assertSee('Leads calientes')
            ->assertSee('Seguimientos pendientes')
            ->assertSee('WhatsApp habilitado');
    }

    public function test_admin_menu_service_widgets_summarize_landing_leads_and_pipeline(): void
    {
        config(['analytics.dashboard_days' => 30]);

        $session = AnalyticsSession::create([
            'session_id' => (string) str()->uuid(),
            'visitor_id' => (string) str()->uuid(),
            'landing_url' => 'https://lapsique.media/reels-de-comida',
            'landing_path' => '/reels-de-comida',
            'last_seen_at' => now(),
        ]);

        AnalyticsPageview::create([
            'analytics_session_id' => $session->id,
            'visitor_id' => $session->visitor_id,
            'url' => 'https://lapsique.media/reels-de-comida',
            'path' => '/reels-de-comida',
            'title' => 'Comida',
        ]);

        AnalyticsEvent::create([
            'analytics_session_id' => $session->id,
            'visitor_id' => $session->visitor_id,
            'name' => 'food_reels_booking_cta_clicked',
            'category' => 'booking_funnel',
            'url' => 'https://lapsique.media/reels-de-comida',
            'path' => '/reels-de-comida',
            'metadata' => [
                'service_type' => 'food_reels',
                'content_category' => 'food_reels_booking',
            ],
        ]);

        AnalyticsEvent::create([
            'analytics_session_id' => $session->id,
            'visitor_id' => $session->visitor_id,
            'name' => 'food_reels_whatsapp_cta_clicked',
            'category' => 'booking_funnel',
            'url' => 'https://lapsique.media/reels-de-comida',
            'path' => '/reels-de-comida',
            'metadata' => [
                'service_type' => 'food_reels',
                'content_category' => 'food_reels_booking',
            ],
        ]);

        $slot = BookingSlot::create([
            'date' => now()->addDays(2)->toDateString(),
            'time_label' => '2:00 PM',
            'time_value' => '14:00',
            'max_bookings' => 3,
            'booked_count' => 1,
            'is_active' => true,
        ]);

        $this->createBooking($slot, [
            'client_name' => 'Restaurante Lead',
            'client_email' => 'food@example.com',
            'service_type' => ContentBooking::SERVICE_CONTENT_SESSION,
            'amount' => 5000,
            'status' => 'pending_payment',
            'landing_url' => 'https://lapsique.media/reels-de-comida',
        ]);

        Livewire::test(ContentBookingMenuServicesOverviewWidget::class)
            ->assertSee('Comida')
            ->assertSee('1 leads / 1 reservas')
            ->assertSee('1 visitas')
            ->assertSee('1 WhatsApp');

        Livewire::test(ContentBookingPipelineTableWidget::class)
            ->assertSee('Restaurante Lead')
            ->assertSee('Comida')
            ->assertSee('Pendiente de pago');
    }

    public function test_admin_can_create_manual_booking_for_any_session_service(): void
    {
        $user = User::factory()->create();
        $slot = BookingSlot::create([
            'date' => now()->addDays(4)->toDateString(),
            'time_label' => '5:00 PM',
            'time_value' => '17:00',
            'max_bookings' => 1,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(CreateContentBooking::class)
            ->fillForm([
                'booking_slot_id' => $slot->id,
                'service_type' => ContentBooking::SERVICE_CONSTRUCTION_PROGRESS,
                'client_name' => 'Constructora Manual',
                'client_email' => 'obra@example.com',
                'client_phone' => '529841234567',
                'notes' => 'Avance de obra creado desde Filament.',
                'shoot_location' => 'Tulum',
                'status' => 'pending_payment',
                'payment_provider' => 'internal',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $booking = ContentBooking::query()->where('client_email', 'obra@example.com')->firstOrFail();

        $this->assertSame(ContentBooking::SERVICE_CONSTRUCTION_PROGRESS, $booking->service_type);
        $this->assertSame(5000, $booking->amount);
        $this->assertSame('internal', $booking->payment_provider);
        $this->assertSame(1, $slot->fresh()->booked_count);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createBooking(BookingSlot $slot, array $overrides = []): ContentBooking
    {
        return ContentBooking::create(array_merge([
            'public_id' => (string) str()->uuid(),
            'booking_slot_id' => $slot->id,
            'service_type' => ContentBooking::SERVICE_CONTENT_SESSION,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
        ], $overrides));
    }
}
