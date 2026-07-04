<?php

namespace Tests\Feature;

use App\Filament\Pages\SalesHub;
use App\Filament\Pages\ContentBookingSalesDashboard;
use App\Filament\Resources\BookingSlots\BookingSlotResource;
use App\Filament\Resources\ContentBookings\Pages\CreateContentBooking;
use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Filament\Widgets\ContentBookingSalesOrdersTableWidget;
use App\Filament\Widgets\ContentBookingSalesOverviewWidget;
use App\Models\BookingSlot;
use App\Models\ContentBooking;
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
            'status' => 'pending_payment',
            'payment_provider' => 'stripe',
        ]);

        $this->createBooking($slot, [
            'client_name' => 'Cliente Fallido',
            'client_email' => 'fallido@example.com',
            'status' => 'failed',
            'payment_provider' => 'stripe',
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
            ->get(SalesHub::getUrl())
            ->assertOk()
            ->assertSee('Ventas')
            ->assertSee('Ver reservas de sesiones')
            ->assertSee('Ver clientes de sesiones');

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
