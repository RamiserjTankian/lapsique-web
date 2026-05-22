<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CanResetPassword $customer,
        public string $token,
        public string $resetUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablecer contraseña — Lapsique',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'customer' => $this->customer,
                'resetUrl' => $this->resetUrl,
                'recipientEmail' => $this->customer->getEmailForPasswordReset(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
