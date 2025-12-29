<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketingEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Customer $customer,
        public string $subject,
        public string $emailContent,
        public string $trackingToken,
        public ?string $buttonText = null,
        public ?string $buttonUrl = null
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Función helper para trackear links en el contenido HTML
        $trackedContent = $this->trackLinksInContent($this->emailContent, $this->trackingToken);

        return new Content(
            view: 'emails.marketing',
            with: [
                'customer' => $this->customer,
                'emailContent' => $trackedContent,
                'trackingToken' => $this->trackingToken,
                'trackingPixelUrl' => route('email.track.open', ['token' => $this->trackingToken]),
                'unsubscribeUrl' => route('customer.unsubscribe', ['email' => $this->customer->email]),
                'buttonText' => $this->buttonText,
                'buttonUrl' => $this->buttonUrl ? route('email.track.click', [
                    'token' => $this->trackingToken,
                    'url' => $this->buttonUrl
                ]) : null,
            ],
        );
    }

    /**
     * Track all links in HTML content
     */
    protected function trackLinksInContent(string $content, string $trackingToken): string
    {
        // Pattern to match <a> tags with href
        $pattern = '/<a\s+([^>]*href=["\']([^"\']*)["\'][^>]*)>/i';
        
        return preg_replace_callback($pattern, function ($matches) use ($trackingToken) {
            $fullTag = $matches[0];
            $attributes = $matches[1];
            $url = $matches[2];
            
            // Skip if it's already a tracking URL or mailto/tel link
            if (str_contains($url, route('email.track.click')) || 
                str_starts_with($url, 'mailto:') || 
                str_starts_with($url, 'tel:')) {
                return $fullTag;
            }
            
            // Create tracked URL
            $trackedUrl = route('email.track.click', [
                'token' => $trackingToken,
                'url' => $url
            ]);
            
            // Replace href in attributes
            $newAttributes = preg_replace(
                '/href=["\']([^"\']*)["\']/i',
                'href="' . $trackedUrl . '"',
                $attributes
            );
            
            return '<a ' . $newAttributes . '>';
        }, $content);
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
}
