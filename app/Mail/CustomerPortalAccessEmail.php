<?php

namespace App\Mail;

use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\TicketOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPortalAccessEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public string $temporaryPassword,
        public string $trackingToken,
        public ?TicketOrder $order = null,
        public ?ContentBooking $booking = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido a tu portal Lapsique — datos de acceso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-portal-access',
            with: [
                'customer' => $this->customer,
                'order' => $this->order,
                'portalUrl' => route('customers.portal'),
                'loginUrl' => route('customers.login'),
                'temporaryPassword' => $this->temporaryPassword,
                'trackingToken' => $this->trackingToken,
                'booking' => $this->booking,
                'trackingPixelUrl' => route('email.track.open', ['token' => $this->trackingToken]),
                'recipientEmail' => $this->customer->email,
                'unsubscribeUrl' => route('customer.unsubscribe', ['email' => $this->customer->email]),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
