<?php

namespace App\Mail;

use App\Models\ContentBooking;
use App\Models\ContentBookingDeliverableLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContentBookingDeliverablesReadyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContentBooking $booking,
        public ContentBookingDeliverableLink $deliverableLink,
        public string $trackingToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu contenido Lapsique ya está listo',
        );
    }

    public function content(): Content
    {
        $booking = $this->booking->loadMissing(['slot', 'deliverableLinks']);

        return new Content(
            view: 'emails.content-booking-deliverables-ready',
            with: [
                'booking' => $booking,
                'slot' => $booking->slot,
                'deliverableLink' => $this->deliverableLink,
                'allLinks' => $booking->deliverableLinks,
                'driveUrl' => $this->deliverableLink->url,
                'portalUrl' => route('customers.portal'),
                'trackingPixelUrl' => route('email.track.open', ['token' => $this->trackingToken]),
                'recipientEmail' => $booking->client_email,
                'unsubscribeUrl' => route('customer.unsubscribe', ['email' => $booking->client_email]),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
