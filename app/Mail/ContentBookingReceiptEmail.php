<?php

namespace App\Mail;

use App\Models\ContentBooking;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContentBookingReceiptEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContentBooking $booking,
        public string $trackingToken,
        public ?Customer $customer = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recibo de tu reserva de '.$this->booking->service_short_name.' Lapsique',
        );
    }

    public function content(): Content
    {
        $booking = $this->booking->loadMissing('slot', 'customer');
        $customer = $this->customer ?? $booking->customer;

        return new Content(
            view: 'emails.content-booking-receipt',
            with: [
                'booking' => $booking,
                'slot' => $booking->slot,
                'customer' => $customer,
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
