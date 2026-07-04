<?php

namespace App\Services;

use App\Jobs\SendContentBookingDeliverablesReadyEmailJob;
use App\Models\ContentBooking;
use App\Models\ContentBookingDeliverableLink;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ContentBookingDeliverablesService
{
    public function addDriveLink(ContentBooking $booking, string $url, ?string $label = null): ContentBookingDeliverableLink
    {
        $normalizedUrl = $this->normalizeUrl($url);

        if (! $this->isAllowedDriveUrl($normalizedUrl)) {
            throw new InvalidArgumentException('El enlace debe ser una URL válida de Google Drive.');
        }

        $link = $booking->deliverableLinks()->create([
            'label' => filled($label) ? trim($label) : $this->defaultLabel($booking),
            'url' => $normalizedUrl,
        ]);

        $booking->update([
            'deliverables_drive_url' => $normalizedUrl,
            'deliverables_ready_at' => $booking->deliverables_ready_at ?? now(),
        ]);

        SendContentBookingDeliverablesReadyEmailJob::dispatchAfterResponse(
            $booking->fresh(['slot', 'customer', 'deliverableLinks']),
            $link,
        );

        return $link;
    }

    public function normalizeUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            throw new InvalidArgumentException('El enlace no puede estar vacío.');
        }

        if (! Str::startsWith($url, ['http://', 'https://'])) {
            $url = 'https://'.$url;
        }

        return $url;
    }

    public function isAllowedDriveUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return Str::contains($host, [
            'drive.google.com',
            'docs.google.com',
            'drive.googleusercontent.com',
        ]);
    }

    protected function defaultLabel(ContentBooking $booking): string
    {
        $count = $booking->deliverableLinks()->count() + 1;

        return $count > 1 ? "Entrega #{$count}" : 'Tu '.$booking->service_short_name;
    }
}
