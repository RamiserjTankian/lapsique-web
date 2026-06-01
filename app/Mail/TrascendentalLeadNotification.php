<?php

namespace App\Mail;

use App\Models\ContactLog;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrascendentalLeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public ContactLog $contactLog,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo lead Trascendental - '.$this->customer->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trascendental-lead-notification',
            with: [
                'customer' => $this->customer,
                'contactLog' => $this->contactLog,
                'lead' => $this->contactLog->metadata ?? [],
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
