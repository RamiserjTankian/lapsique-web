<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrascendentalJoinListConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public string $discountCode,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Trascendental - 20% para tu siguiente evento',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trascendental-join-list-confirmation',
            with: [
                'customer' => $this->customer,
                'discountCode' => $this->discountCode,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
