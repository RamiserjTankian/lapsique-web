<?php

namespace Tests\Feature;

use App\Mail\CustomerPasswordResetEmail;
use App\Mail\MailtrapTestEmail;
use App\Mail\WelcomeEmail;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\Event;
use App\Models\GuestListEntry;
use App\Notifications\CustomerResetPasswordNotification;
use App\Support\EmailBrand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_contains_brand_tokens(): void
    {
        $html = View::make('emails.welcome', [
            'customer' => $this->makeCustomer(),
        ])->render();

        $this->assertBrandTokens($html);
        $this->assertStringContainsString(EmailBrand::WORDMARK, $html);
        $this->assertStringContainsString(EmailBrand::TAGLINE, $html);
        $this->assertStringContainsString('Ya estás dentro', $html);
        $this->assertStringContainsString('DJ sets y escena', $html);
        $this->assertStringNotContainsString('Techno & Electronic Music', $html);
        $this->assertStringNotContainsString('Estamos emocionados', $html);
    }

    public function test_marketing_email_has_single_tracking_pixel_when_url_provided(): void
    {
        $html = View::make('emails.marketing', [
            'customer' => $this->makeCustomer(),
            'emailContent' => '<p>Contenido de prueba</p>',
            'buttonUrl' => null,
            'buttonText' => null,
            'trackingPixelUrl' => 'https://example.com/pixel.gif',
        ])->render();

        $this->assertBrandTokens($html);
        $this->assertSame(1, substr_count($html, 'https://example.com/pixel.gif'));
    }

    public function test_password_reset_notification_returns_branded_mailable(): void
    {
        $customer = $this->makeCustomer();
        $notification = new CustomerResetPasswordNotification('test-token');

        $mailable = $notification->toMail($customer);

        $this->assertInstanceOf(CustomerPasswordResetEmail::class, $mailable);

        $html = View::make('emails.password-reset', [
            'customer' => $customer,
            'resetUrl' => 'https://lapsique.test/reset',
            'recipientEmail' => $customer->email,
        ])->render();

        $this->assertBrandTokens($html);
        $this->assertStringContainsString('Restablecer contraseña', $html);
    }

    public function test_event_confirmation_uses_real_event_time(): void
    {
        $customer = $this->makeCustomer();
        $event = Event::create([
            'title' => 'Noche Lapsique',
            'slug' => 'noche-lapsique',
            'starts_at' => now()->addWeek()->setTime(21, 30),
        ]);
        $entry = GuestListEntry::create([
            'event_id' => $event->id,
            'customer_id' => $customer->id,
            'status' => 'confirmed',
        ]);

        $html = View::make('emails.event-confirmation', [
            'customer' => $customer,
            'event' => $event,
            'guestListEntry' => $entry,
            'checkInUrl' => $entry->getCheckInUrl(),
            'checkInQrUrl' => $entry->getCheckInQrUrl(),
            'checkInCode' => $entry->getCheckInCode(),
            'eventUrl' => 'https://lapsique.test/events/'.$event->slug,
        ])->render();

        $this->assertBrandTokens($html);
        $this->assertStringContainsString('21:30', $html);
        $this->assertStringContainsString('21:00', $html);
        $this->assertStringNotContainsString('12:00', $html);
    }

    public function test_ticket_prospect_uses_light_brand_palette(): void
    {
        $event = Event::create([
            'title' => 'Evento Test',
            'slug' => 'evento-test',
            'starts_at' => now()->addDays(3),
        ]);

        $order = (object) [
            'buyer_name' => 'Comprador',
            'subtotal' => 1000,
            'fee' => 150,
            'total' => 1150,
            'currency' => 'MXN',
        ];

        $items = collect([
            (object) ['quantity' => 2, 'name' => 'General', 'unit_price' => 500],
        ]);

        $html = View::make('emails.ticket-prospect', [
            'order' => $order,
            'event' => $event,
            'items' => $items,
            'manageUrl' => 'https://lapsique.test/tickets/manage',
        ])->render();

        $this->assertBrandTokens($html);
        $this->assertStringContainsString('el pago todavía no quedó cerrado', $html);
        $this->assertStringContainsString('Tus accesos no se emiten', $html);
        $this->assertStringNotContainsString('Quedaste suscrito a nuestro newsletter', $html);
        $this->assertStringNotContainsString('#071E2A', $html);
        $this->assertStringNotContainsString('#1B82A4', $html);
    }

    public function test_content_booking_receipt_title_is_consistent(): void
    {
        $booking = ContentBooking::create([
            'public_id' => (string) Str::uuid(),
            'service_type' => ContentBooking::SERVICE_CONTENT_SESSION,
            'client_name' => 'Cliente',
            'client_email' => 'cliente@example.com',
            'client_phone' => '529841234567',
            'amount' => 3000,
            'currency' => 'MXN',
            'status' => 'confirmed',
            'paid_at' => now(),
            'payment_provider' => 'stripe',
        ]);

        $html = View::make('emails.content-booking-receipt', [
            'booking' => $booking,
            'slot' => null,
            'customer' => null,
            'portalUrl' => 'https://lapsique.test/portal',
        ])->render();

        $this->assertBrandTokens($html);
        $this->assertStringContainsString('Recibo de pago', $html);
        $this->assertStringNotContainsString('Recibo de compra', $html);
    }

    public function test_mailtrap_test_template_renders(): void
    {
        $html = View::make('emails.mailtrap-test', [
            'sentAt' => '2026-05-22 12:00:00',
        ])->render();

        $this->assertBrandTokens($html);
        $this->assertStringContainsString('Mailtrap operativo', $html);
    }

    public function test_welcome_mailable_renders_with_brand(): void
    {
        $customer = $this->makeCustomer();
        $mailable = new WelcomeEmail($customer, 'token-welcome');

        $mailable->assertHasSubject('¡Bienvenido a Lapsique! 🎧');
        $mailable->assertSeeInHtml(EmailBrand::WORDMARK);
        $mailable->assertSeeInHtml(EmailBrand::BACKGROUND);
    }

    public function test_mailtrap_test_mailable_renders(): void
    {
        $mailable = new MailtrapTestEmail;

        $mailable->assertSeeInHtml(EmailBrand::WORDMARK);
        $mailable->assertSeeInHtml('Mailtrap operativo');
    }

    public function test_forgot_password_dispatches_reset_notification(): void
    {
        Notification::fake();

        $customer = $this->makeCustomer();
        $customer->sendPasswordResetNotification('reset-token-test');

        Notification::assertSentTo(
            $customer,
            CustomerResetPasswordNotification::class,
            fn (CustomerResetPasswordNotification $notification): bool => $notification->token === 'reset-token-test'
        );
    }

    protected function makeCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Cliente Test',
            'email' => 'cliente-test@example.com',
            'status' => 'lead',
        ]);
    }

    protected function assertBrandTokens(string $html): void
    {
        $this->assertStringContainsString(EmailBrand::BACKGROUND, $html);
        $this->assertStringContainsString(EmailBrand::PRIMARY, $html);
        $this->assertStringContainsString('DM Sans', $html);
        $this->assertStringContainsString('Syne', $html);
    }
}
