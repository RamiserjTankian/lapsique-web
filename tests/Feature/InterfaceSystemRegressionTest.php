<?php

namespace Tests\Feature;

use Tests\TestCase;

class InterfaceSystemRegressionTest extends TestCase
{
    public function test_admin_login_uses_lapsique_branding(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Lapsique Media')
            ->assertDontSee('TRASCENDENTAL.');
    }

    public function test_shared_public_layouts_expose_skip_links_and_unique_main_targets(): void
    {
        $siteLayout = file_get_contents(resource_path('js/layouts/SiteLayout.tsx'));
        $trascendentalLayout = file_get_contents(resource_path('js/layouts/TrascendentalLayout.tsx'));

        $this->assertIsString($siteLayout);
        $this->assertIsString($trascendentalLayout);
        $this->assertStringContainsString('href="#main-content"', $siteLayout);
        $this->assertStringContainsString('id="main-content"', $siteLayout);
        $this->assertStringContainsString('href="#trascendental-main"', $trascendentalLayout);
        $this->assertStringContainsString('id="trascendental-main"', $trascendentalLayout);
    }

    public function test_editorial_archives_do_not_expose_native_video_controls(): void
    {
        $videosIndex = file_get_contents(resource_path('js/pages/Videos/Index.tsx'));
        $mediaViewer = file_get_contents(resource_path('js/components/lapsique/PortfolioMediaViewer.tsx'));

        $this->assertIsString($videosIndex);
        $this->assertIsString($mediaViewer);
        $this->assertStringContainsString('EditorialVideoPlayer', $videosIndex);
        $this->assertStringContainsString('EditorialVideoPlayer', $mediaViewer);
        $this->assertStringNotContainsString('<video controls', $videosIndex);
        $this->assertStringNotContainsString('<video controls', $mediaViewer);
    }

    public function test_application_interface_sources_avoid_transition_all(): void
    {
        $files = array_merge(
            glob(resource_path('js/**/*.tsx')) ?: [],
            glob(resource_path('js/**/**/*.tsx')) ?: [],
            glob(resource_path('js/**/**/**/*.tsx')) ?: [],
        );

        foreach (array_unique($files) as $file) {
            $source = file_get_contents($file);

            $this->assertIsString($source);
            $this->assertStringNotContainsString('transition-all', $source, $file);
        }
    }
}
