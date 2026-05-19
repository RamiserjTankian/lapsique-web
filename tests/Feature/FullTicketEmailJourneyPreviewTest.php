<?php

namespace Tests\Feature;

use App\Mail\CustomerPortalAccessEmail;
use App\Mail\TicketAccessEmail;
use App\Mail\TicketOrderConfirmationEmail;
use App\Mail\TicketProspectEmail;
use App\Mail\WelcomeEmail;
use App\Models\Customer;
use App\Models\Event;
use App\Models\TicketProduct;
use App\Services\TicketOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class FullTicketEmailJourneyPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_full_email_journey_previews_for_ramiro_diaz_ramos(): void
    {
        Queue::fake();
        config()->set('services.mailtrap.api_token', '');

        $previewDirectory = storage_path('app/testing-mail-previews/ramiro-diaz-ramos-full-sale');
        File::deleteDirectory($previewDirectory);
        File::ensureDirectoryExists($previewDirectory . '/attachments');

        $recipient = [
            'name' => 'Ramiro Diaz Ramos',
            'email' => 'ramiro@bluepointrs.com',
            'phone' => '7444372758',
            'whatsapp' => '7444372758',
        ];

        $customer = Customer::create([
            'name' => $recipient['name'],
            'email' => $recipient['email'],
            'phone' => $recipient['phone'],
            'whatsapp' => $recipient['whatsapp'],
            'status' => 'lead',
            'source' => 'website',
            'subscribed_newsletter' => true,
            'subscribed_sms' => true,
            'subscribed_whatsapp' => true,
            'last_interaction_at' => now(),
        ]);

        $event = Event::create([
            'title' => 'REBOLLEDO at Zal Marina',
            'slug' => 'rebolledo-at-zal-marina-preview',
            'headline' => 'Sunset electronic experience',
            'description' => 'Preview journey for transactional email design review.',
            'starts_at' => Carbon::parse('2026-04-18 19:00:00'),
            'venue' => 'Zal Marina',
            'city' => 'Progreso, Yucatán',
        ]);

        $table = TicketProduct::create([
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

        $orderService = app(TicketOrderService::class);

        $welcomeEmail = new WelcomeEmail($customer->fresh(), 'preview-token-01');

        $order = $orderService->createOrder(
            $event,
            [$table->id => 1],
            [
                'name' => $recipient['name'],
                'email' => $recipient['email'],
                'whatsapp' => $recipient['whatsapp'],
                'phone' => $recipient['phone'],
            ]
        )->fresh(['event', 'items', 'customer']);

        $prospectEmail = new TicketProspectEmail($order, 'preview-token-02');

        $orderService->syncStripePaymentIntent($order, [
            'id' => 'pi_preview_ramiro_full_sale',
            'status' => 'succeeded',
            'payment_method' => 'pm_preview_ramiro_full_sale',
        ]);

        $order = $order->fresh(['event', 'items', 'customer', 'attendees']);

        $portalEmail = new CustomerPortalAccessEmail(
            $order->customer,
            'ZalMarina2026',
            'preview-token-03',
            $order
        );

        $orderConfirmationEmail = new TicketOrderConfirmationEmail($order, 'preview-token-04');

        $firstAttendee = $order->attendees()->orderBy('id')->firstOrFail();

        $response = $this->post(route('tickets.attendees.store', $order), [
            'attendees' => [
                $firstAttendee->id => [
                    'name' => $recipient['name'],
                    'email' => $recipient['email'],
                    'whatsapp' => $recipient['whatsapp'],
                    'instagram_handle' => '@ramirodiazramos',
                ],
            ],
        ]);

        $response->assertRedirect(route('tickets.success', $order));

        $firstAttendee->refresh()->loadMissing('event', 'product', 'order');

        $ticketAccessEmail = new TicketAccessEmail($firstAttendee, 'preview-token-05');

        $previews = [
            $this->exportMailablePreview($previewDirectory, '01-welcome', $recipient['email'], $welcomeEmail),
            $this->exportMailablePreview($previewDirectory, '02-prospect', $recipient['email'], $prospectEmail),
            $this->exportMailablePreview($previewDirectory, '03-portal-access', $recipient['email'], $portalEmail),
            $this->exportMailablePreview($previewDirectory, '04-order-confirmation', $recipient['email'], $orderConfirmationEmail),
            $this->exportMailablePreview($previewDirectory, '05-ticket-access', $recipient['email'], $ticketAccessEmail),
        ];

        $this->writePreviewIndex($previewDirectory, $recipient, $event->title, $previews);

        $this->assertSame('paid', $order->status);
        $this->assertSame(6, $order->attendees()->count());
        $this->assertSame(1, $order->fresh()->attendees_registered);
        $this->assertFileExists($previewDirectory . '/index.html');
        $this->assertFileExists($previewDirectory . '/04-order-confirmation.html');
        $this->assertFileExists($previewDirectory . '/05-ticket-access.html');
        $this->assertFileExists($previewDirectory . '/attachments/04-order-confirmation-pase-rebolledo-at-zal-marina.pdf');
        $this->assertFileExists($previewDirectory . '/attachments/05-ticket-access-pase-' . $firstAttendee->id . '.pdf');
    }

    protected function exportMailablePreview(string $previewDirectory, string $slug, string $recipientEmail, Mailable $mailable): array
    {
        $this->prepareMailableForPreview($mailable);

        $html = $mailable->render();
        $subject = $mailable->subject ?: $mailable->envelope()->subject;

        $htmlPath = $previewDirectory . '/' . $slug . '.html';
        File::put($htmlPath, $html);

        $attachments = $this->extractRawAttachments($mailable);
        $attachmentLinks = [];

        foreach ($attachments as $attachment) {
            $filename = $slug . '-' . $attachment['name'];
            $attachmentPath = $previewDirectory . '/attachments/' . $filename;
            File::put($attachmentPath, $attachment['data']);
            $attachmentLinks[] = [
                'name' => $filename,
                'path' => 'attachments/' . $filename,
                'mime' => $attachment['options']['mime'] ?? 'application/octet-stream',
            ];
        }

        return [
            'slug' => $slug,
            'subject' => $subject,
            'recipient' => $recipientEmail,
            'html_file' => basename($htmlPath),
            'attachments' => $attachmentLinks,
        ];
    }

    protected function writePreviewIndex(string $previewDirectory, array $recipient, string $eventTitle, array $previews): void
    {
        $items = collect($previews)->map(function (array $preview): string {
            $attachmentsHtml = collect($preview['attachments'])
                ->map(fn (array $attachment): string => sprintf(
                    '<li><a href="%s">%s</a> <span style="color:#6b7f8e;">(%s)</span></li>',
                    e($attachment['path']),
                    e($attachment['name']),
                    e($attachment['mime'])
                ))
                ->implode('');

            return sprintf(
                '<section style="border:1px solid #d8e3ea;border-radius:12px;padding:18px 20px;margin:0 0 18px;background:#fff;">
                    <h2 style="margin:0 0 6px;font-size:18px;color:#071e2a;">%s</h2>
                    <p style="margin:0 0 12px;color:#4a6070;font-size:14px;">Para: %s</p>
                    <p style="margin:0 0 12px;"><a href="%s">Abrir HTML</a></p>
                    %s
                </section>',
                e($preview['subject']),
                e($preview['recipient']),
                e($preview['html_file']),
                $attachmentsHtml !== '' ? '<ul style="margin:0;padding-left:18px;">' . $attachmentsHtml . '</ul>' : '<p style="margin:0;color:#6b7f8e;">Sin adjuntos</p>'
            );
        })->implode("\n");

        $index = sprintf(
            '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Preview correos %s</title>
            </head>
            <body style="margin:0;padding:32px;background:#f7f0e6;font-family:Arial,sans-serif;color:#1a2d3d;">
                <div style="max-width:900px;margin:0 auto;">
                    <h1 style="margin:0 0 10px;color:#071e2a;">Preview de venta completa</h1>
                    <p style="margin:0 0 6px;">Cliente: <strong>%s</strong></p>
                    <p style="margin:0 0 6px;">Email: <strong>%s</strong></p>
                    <p style="margin:0 0 6px;">WhatsApp: <strong>%s</strong></p>
                    <p style="margin:0 0 24px;">Evento: <strong>%s</strong></p>
                    %s
                </div>
            </body>
            </html>',
            e($recipient['name']),
            e($recipient['name']),
            e($recipient['email']),
            e($recipient['whatsapp']),
            e($eventTitle),
            $items
        );

        File::put($previewDirectory . '/index.html', $index);
    }

    protected function prepareMailableForPreview(Mailable $mailable): void
    {
        $method = new ReflectionMethod($mailable, 'prepareMailableForDelivery');
        $method->setAccessible(true);
        $method->invoke($mailable);
    }

    protected function extractRawAttachments(Mailable $mailable): array
    {
        $reflection = new ReflectionClass($mailable);
        $rawAttachments = $reflection->getProperty('rawAttachments');
        $rawAttachments->setAccessible(true);

        return collect($rawAttachments->getValue($mailable))
            ->unique(fn (array $attachment): string => ($attachment['name'] ?? '') . '|' . ($attachment['options']['mime'] ?? ''))
            ->values()
            ->all();
    }
}
