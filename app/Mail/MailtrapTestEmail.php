<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailtrapTestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Prueba Mailtrap — lapsique.media',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mailtrap-test',
            with: [
                'sentAt' => now()->toDateTimeString(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
