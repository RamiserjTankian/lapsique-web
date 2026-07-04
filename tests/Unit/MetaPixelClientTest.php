<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MetaPixelClientTest extends TestCase
{
    public function test_pixel_client_supports_inertia_and_blade_queues_with_event_ids(): void
    {
        $pixelSource = file_get_contents(__DIR__.'/../../resources/js/pixel.js');
        $inertiaLayout = file_get_contents(__DIR__.'/../../resources/views/app.blade.php');
        $bladeLayout = file_get_contents(__DIR__.'/../../resources/views/layouts/site.blade.php');

        $this->assertStringContainsString('window.SitePixel || window.LapsiquePixel', $pixelSource);
        $this->assertStringContainsString('window.__sitePixelQueue', $pixelSource);
        $this->assertStringContainsString('window.__lapsiquePixelQueue', $pixelSource);
        $this->assertStringContainsString('queuedCall.options', $pixelSource);

        $this->assertStringContainsString('function (eventName, payload, options)', $inertiaLayout);
        $this->assertStringContainsString('options: options || null', $inertiaLayout);
        $this->assertStringContainsString('function (eventName, payload, options)', $bladeLayout);
        $this->assertStringContainsString('options: options || null', $bladeLayout);
    }
}
