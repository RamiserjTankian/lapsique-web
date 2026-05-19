<?php

namespace Tests\Unit;

use App\Models\ContentBooking;
use App\Services\ContentBookingDeliverablesService;
use InvalidArgumentException;
use Tests\TestCase;

class ContentBookingDeliverablesServiceTest extends TestCase
{
    public function test_rejects_non_drive_urls(): void
    {
        $service = app(ContentBookingDeliverablesService::class);

        $this->expectException(InvalidArgumentException::class);

        $service->addDriveLink(
            new ContentBooking(['client_email' => 'test@example.com']),
            'https://example.com/file.zip',
        );
    }

    public function test_normalizes_drive_url(): void
    {
        $service = app(ContentBookingDeliverablesService::class);

        $url = $service->normalizeUrl('drive.google.com/drive/folders/abc123');

        $this->assertSame('https://drive.google.com/drive/folders/abc123', $url);
        $this->assertTrue($service->isAllowedDriveUrl($url));
    }
}
