<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SiteHeaderNavigationTest extends TestCase
{
    #[Test]
    public function desktop_dropdowns_use_stable_click_and_focus_states(): void
    {
        $source = file_get_contents(resource_path('js/components/lapsique/SiteHeader.tsx'));

        $this->assertIsString($source);
        $this->assertStringContainsString('value={group.id}', $source);
        $this->assertStringNotContainsString('onPointerMove={(event) => event.preventDefault()}', $source);
        $this->assertStringContainsString('md:!mt-0', $source);
        $this->assertStringContainsString('focus:!text-foreground', $source);
        $this->assertStringContainsString('data-[state=open]:focus:!text-primary', $source);
        $this->assertStringContainsString('data-[state=closed]:!text-foreground', $source);
        $this->assertStringContainsString('data-[active=true]:!text-foreground', $source);
    }
}
