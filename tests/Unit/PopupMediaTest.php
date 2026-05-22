<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PopupMediaTest extends TestCase
{
    #[Test]
    public function popup_media_module_is_available_for_frontend_build(): void
    {
        $path = resource_path('js/lib/popupMedia.ts');

        $this->assertFileExists($path);
        $this->assertStringContainsString('resolvePopupImage', file_get_contents($path));
        $this->assertStringContainsString('djset', file_get_contents($path));
    }
}
