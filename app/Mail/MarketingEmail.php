<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\User;
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
        public string $emailSubject,
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
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $trackedButtonUrl = $this->buttonUrl ? route('email.track.click', [
            'token' => $this->trackingToken,
            'url' => $this->buttonUrl,
        ]) : null;
        $profileImageUrl = $this->getSenderProfileImageUrl();

        // Convertir URLs de imágenes relativas a absolutas
        $contentWithImages = $this->convertImageUrlsToAbsolute($this->emailContent, $trackedButtonUrl);
        
        // Función helper para trackear links en el contenido HTML
        $trackedContent = $this->trackLinksInContent($contentWithImages, $this->trackingToken);

        return new Content(
            view: 'emails.marketing',
            with: [
                'customer' => $this->customer,
                'emailContent' => $trackedContent,
                'trackingToken' => $this->trackingToken,
                'trackingPixelUrl' => route('email.track.open', ['token' => $this->trackingToken]),
                'unsubscribeUrl' => route('customer.unsubscribe', ['email' => $this->customer->email]),
                'buttonText' => $this->buttonText,
                'buttonUrl' => $trackedButtonUrl,
                'profileImageUrl' => $profileImageUrl,
            ],
        );
    }

    /**
     * Convert relative image URLs to absolute URLs
     */
    protected function convertImageUrlsToAbsolute(string $content, ?string $linkUrl = null): string
    {
        if (trim($content) === '') {
            return $content;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="email-content">' . $content . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $container = $dom->getElementById('email-content');

        if (! $container) {
            return $content;
        }

        $images = [];
        foreach ($container->getElementsByTagName('img') as $image) {
            $images[] = $image;
        }

        foreach ($images as $image) {
            $src = $image->getAttribute('src');

            if ($src === '') {
                continue;
            }

            if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
                $absoluteUrl = $src;
            } elseif (str_starts_with($src, '/storage/')) {
                $absoluteUrl = url($src);
            } elseif (str_contains($src, 'storage/') || str_contains($src, 'campaigns/attachments')) {
                if (!str_starts_with($src, '/')) {
                    $src = '/' . $src;
                }
                $absoluteUrl = url($src);
            } else {
                $absoluteUrl = url($src);
            }

            $image->setAttribute('src', $absoluteUrl);

            if (! $image->hasAttribute('width')) {
                $image->setAttribute('width', '600');
            }

            if (! $image->hasAttribute('align')) {
                $image->setAttribute('align', 'center');
            }

            $image->setAttribute(
                'style',
                $this->mergeInlineStyles($image->getAttribute('style'), [
                    'max-width' => '600px',
                    'width' => '100%',
                    'height' => 'auto',
                    'display' => 'block',
                    'margin' => '0 auto',
                    'border' => '0',
                ])
            );

            $wrapperTarget = $image;
            $parent = $image->parentNode;

            if ($linkUrl) {
                if ($parent && strtolower($parent->nodeName) === 'a') {
                    $parent->setAttribute('href', $linkUrl);
                    $parent->setAttribute(
                        'style',
                        $this->mergeInlineStyles($parent->getAttribute('style'), [
                            'text-decoration' => 'none',
                        ])
                    );
                    $wrapperTarget = $parent;
                } else {
                    $anchor = $dom->createElement('a');
                    $anchor->setAttribute('href', $linkUrl);
                    $anchor->setAttribute('style', 'text-decoration: none;');
                    $parent?->replaceChild($anchor, $image);
                    $anchor->appendChild($image);
                    $wrapperTarget = $anchor;
                }
            }

            $currentParent = $wrapperTarget->parentNode;

            if ($currentParent) {
                $table = $dom->createElement('table');
                $table->setAttribute('role', 'presentation');
                $table->setAttribute('width', '100%');
                $table->setAttribute('cellpadding', '0');
                $table->setAttribute('cellspacing', '0');
                $table->setAttribute('border', '0');
                $table->setAttribute('style', 'width: 100%; border-collapse: collapse; margin: 16px 0;');

                $tbody = $dom->createElement('tbody');
                $tr = $dom->createElement('tr');
                $td = $dom->createElement('td');
                $td->setAttribute('align', 'center');
                $td->setAttribute('style', 'text-align: center;');

                $currentParent->replaceChild($table, $wrapperTarget);
                $table->appendChild($tbody);
                $tbody->appendChild($tr);
                $tr->appendChild($td);
                $td->appendChild($wrapperTarget);
            }
        }

        $output = '';
        foreach ($container->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return $output;
    }

    /**
     * @param array<string, string> $styles
     */
    protected function mergeInlineStyles(string $existing, array $styles): string
    {
        $style = trim($existing);
        $styleLower = strtolower($style);

        foreach ($styles as $property => $value) {
            $needle = strtolower($property) . ':';
            if (!str_contains($styleLower, $needle)) {
                if ($style !== '' && !str_ends_with(trim($style), ';')) {
                    $style .= ';';
                }
                $style .= ' ' . $property . ': ' . $value . ';';
                $styleLower = strtolower($style);
            }
        }

        return trim($style);
    }

    /**
     * Track all links in HTML content
     */
    protected function trackLinksInContent(string $content, string $trackingToken): string
    {
        // Pattern to match <a> tags with href
        $pattern = '/<a\s+([^>]*href=["\']([^"\']*)["\'][^>]*)>/i';
        $trackingBaseUrl = route('email.track.click', ['token' => $trackingToken]);
        
        return preg_replace_callback($pattern, function ($matches) use ($trackingToken, $trackingBaseUrl) {
            $fullTag = $matches[0];
            $attributes = $matches[1];
            $url = $matches[2];
            
            // Skip if it's already a tracking URL or mailto/tel link
            if (str_contains($url, $trackingBaseUrl) || 
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

    protected function getSenderProfileImageUrl(): ?string
    {
        $user = User::find(1);

        if (! $user) {
            return null;
        }

        $url = $user->getFirstMediaUrl('avatar', 'thumb');

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
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
