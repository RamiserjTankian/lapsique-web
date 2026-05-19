<?php

namespace App\Mail;

use App\Models\ContentBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContentBookingConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContentBooking $booking,
        public string $trackingToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu sesión Lapsique está confirmada',
        );
    }

    public function content(): Content
    {
        $booking = $this->booking->loadMissing('slot');

        return new Content(
            view: 'emails.content-booking-confirmation',
            with: [
                'booking' => $booking,
                'slot' => $booking->slot,
                'confirmUrl' => route('booking.confirm', $booking->public_id),
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
