<?php

namespace App\Mail;

use App\Models\TicketOrder;
use App\Services\TicketPassPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketOrderConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TicketOrder $order,
        public string $trackingToken
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "✅ Compra confirmada: {$this->order->event?->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-order-confirmation',
            with: [
                'order' => $this->order,
                'event' => $this->order->event,
                'items' => $this->order->items,
                'trackingToken' => $this->trackingToken,
                'trackingPixelUrl' => route('email.track.open', ['token' => $this->trackingToken]),
                'orderUrl' => route('tickets.success', $this->order),
                'recipientEmail' => $this->order->buyer_email,
                'unsubscribeUrl' => $this->order->buyer_email
                    ? route('customer.unsubscribe', ['email' => $this->order->buyer_email])
                    : route('customer.unsubscribe'),
            ],
        );
    }

    public function attachments(): array
    {
        if ($this->order->status !== 'paid') {
            return [];
        }

        $pdfService = app(TicketPassPdfService::class);
        $pdf = $pdfService->buildForOrder($this->order->loadMissing('event', 'items.attendees'));
        $filename = $pdfService->filenameForEvent($this->order->event);

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
