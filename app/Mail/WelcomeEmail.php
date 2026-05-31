<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Customer $customer,
        public string $trackingToken
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido a Lapsique! 🎧',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'customer' => $this->customer,
                'variant' => $this->welcomeVariant(),
                'ctaUrl' => $this->welcomeCtaUrl(),
                'ctaLabel' => $this->welcomeCtaLabel(),
                'language' => $this->preferredLanguage(),
                'trackingToken' => $this->trackingToken,
                'trackingPixelUrl' => route('email.track.open', ['token' => $this->trackingToken]),
                'unsubscribeUrl' => route('customer.unsubscribe', ['email' => $this->customer->email]),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    protected function welcomeVariant(): string
    {
        $source = strtolower((string) $this->customer->source);
        $tags = collect($this->customer->tags ?? [])->map(fn ($tag): string => strtolower((string) $tag));

        if (str_contains($source, 'dj') || $tags->contains('djs') || $tags->contains('dj_set')) {
            return 'dj_set';
        }

        if (str_contains($source, 'guestlist') || str_contains($source, 'ticket') || $tags->contains('events')) {
            return 'events';
        }

        return 'production';
    }

    protected function welcomeCtaUrl(): string
    {
        return match ($this->welcomeVariant()) {
            'dj_set' => route('djset.show'),
            'events' => route('events.index'),
            default => route('booking.show'),
        };
    }

    protected function welcomeCtaLabel(): string
    {
        $language = $this->preferredLanguage();

        return match ($this->welcomeVariant()) {
            'dj_set' => $language === 'en' ? 'See DJ set sessions' : 'Ver sesiones DJ set',
            'events' => $language === 'en' ? 'See events and tickets' : 'Ver eventos y tickets',
            default => $language === 'en' ? 'Book a content session' : 'Agendar sesión de contenido',
        };
    }

    protected function preferredLanguage(): string
    {
        $metadata = is_array($this->customer->metadata) ? $this->customer->metadata : [];
        $language = strtolower((string) ($metadata['language'] ?? $metadata['locale'] ?? 'es'));

        return str_starts_with($language, 'en') ? 'en' : 'es';
    }
}
