<?php

namespace App\Mail;

use App\Models\TicketOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketProspectEmail extends Mailable
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
            subject: "Completa tu compra en lapsique.media",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-prospect',
            with: [
                'order' => $this->order,
                'event' => $this->order->event,
                'items' => $this->order->items,
                'trackingToken' => $this->trackingToken,
                'trackingPixelUrl' => route('email.track.open', ['token' => $this->trackingToken]),
                'recipientEmail' => $this->order->buyer_email,
                'manageUrl' => $this->order->getManageUrl(),
                'unsubscribeUrl' => $this->order->buyer_email
                    ? route('customer.unsubscribe', ['email' => $this->order->buyer_email])
                    : route('customer.unsubscribe'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
