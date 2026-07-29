<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_inertia_shell_exposes_stable_head_keys(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<title data-inertia="">', false)
            ->assertSee('data-inertia="description" name="description"', false)
            ->assertSee('data-inertia="canonical" rel="canonical"', false)
            ->assertSee('data-inertia="json-ld" type="application/ld+json"', false);
    }

    public function test_private_customer_page_has_meta_and_http_noindex_directives(): void
    {
        $this->get(route('customers.login'))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('data-inertia="robots" name="robots" content="noindex, nofollow"', false);
    }

    public function test_unsubscribe_page_has_meta_and_http_noindex_directives(): void
    {
        $customer = Customer::create([
            'name' => 'SEO Customer',
            'email' => 'seo-customer@example.test',
            'status' => 'lead',
            'source' => 'test',
        ]);

        $this->get(route('customer.unsubscribe', ['email' => $customer->email]))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false);
    }

    public function test_paginated_editorial_index_keeps_page_in_canonical_url(): void
    {
        $this->get(route('videos.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('seo.canonicalUrl', route('videos.index').'?page=2'));
    }

    public function test_content_creation_landing_is_public_canonical_and_in_sitemap(): void
    {
        $this->get(route('content-creation.show'))
            ->assertOk()
            ->assertSee('data-inertia="og-image-type" property="og:image:type" content="image/webp"', false)
            ->assertInertia(fn ($page) => $page
                ->component('ContentCreation/Show')
                ->where('seo.canonicalUrl', route('content-creation.show'))
                ->where('seo.noindex', false));

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('content-creation.show'), false);
    }

    public function test_business_reels_landing_has_distinct_canonical_and_sitemap_entry(): void
    {
        $this->get(route('business-reels.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('ContentCreation/Show')
                ->where('variant', 'business_reels')
                ->where('seo.canonicalUrl', route('business-reels.show'))
                ->where('seo.noindex', false));

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('business-reels.show'), false);
    }

    public function test_home_and_all_service_funnels_expose_contextual_distinct_seo(): void
    {
        $expectedTitles = [
            'home' => 'Más de 200 piezas audiovisuales producidas por Lapsique',
            'content-creation.show' => 'Creación de contenido para redes en Riviera Maya',
            'business-reels.show' => 'Reels para negocios y anuncios en Riviera Maya',
            'food-reels.show' => 'Reels de comida para restaurantes en Riviera Maya',
            'djset.show' => 'Grabación cinematográfica de DJ sets en Riviera Maya',
            'electronic-event-coverage.show' => 'Cobertura de eventos de música electrónica en Riviera Maya',
            'multi-camera.show' => 'Producción multicámara para DJ sets, clubes y eventos',
            'drone-sessions.show' => 'Vuelos con dron para propiedades y campañas en Riviera Maya',
            'construction-progress.show' => 'Avances de obra con dron, foto y video en Riviera Maya',
        ];
        $ogImages = [];

        foreach ($expectedTitles as $routeName => $expectedTitle) {
            $response = $this->withCookie('locale', 'es')->get(route($routeName))->assertOk();
            $seo = $response->inertiaProps('seo');

            $this->assertSame($expectedTitle, $seo['title'], "{$routeName} has the wrong SEO title.");
            $this->assertSame(route($routeName), $seo['canonicalUrl']);
            $this->assertFalse($seo['noindex']);
            $this->assertNotEmpty($seo['description']);
            $this->assertNotEmpty($seo['ogImage']);
            $this->assertNotEmpty($seo['ogImageAlt']);
            $this->assertStringNotContainsString('sesiones exitosas', mb_strtolower($seo['title'].' '.$seo['description']));
            $this->assertStringNotContainsString('trascendental', mb_strtolower($seo['ogImage']));

            $ogImages[] = $seo['ogImage'];
        }

        $this->assertCount(count($expectedTitles), array_unique($ogImages));
    }

    public function test_electronic_event_coverage_landing_is_canonical_and_in_sitemap(): void
    {
        $canonicalUrl = route('electronic-event-coverage.show');
        $ogImage = route('home').'/images/portfolio/video-posters/2026-07-11-mtrx-dumas-a0794b89f7.jpg';

        $this->get($canonicalUrl)
            ->assertOk()
            ->assertSee('data-inertia="canonical" rel="canonical" href="'.$canonicalUrl.'"', false)
            ->assertSee('data-inertia="og-image" property="og:image" content="'.$ogImage.'"', false)
            ->assertSee('data-inertia="twitter-image" name="twitter:image" content="'.$ogImage.'"', false)
            ->assertInertia(fn ($page) => $page
                ->component('EventCoverage/Show')
                ->where('seo.canonicalUrl', $canonicalUrl)
                ->where('seo.ogImage', $ogImage)
                ->where('seo.noindex', false));

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee($canonicalUrl, false);
    }

    public function test_electronic_event_coverage_is_excluded_from_the_trascendental_sitemap(): void
    {
        config()->set('trascendental.enabled_as_primary', true);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertDontSee('/cobertura-eventos-electronica', false);
    }

    public function test_multicamera_landing_is_public_canonical_and_exposes_real_event_material(): void
    {
        $canonicalUrl = route('multi-camera.show');
        $ogImage = route('home').'/images/og/multicamara.jpg';

        $this->get($canonicalUrl)
            ->assertOk()
            ->assertSee('data-inertia="canonical" rel="canonical" href="'.$canonicalUrl.'"', false)
            ->assertSee('data-inertia="og-image" property="og:image" content="'.$ogImage.'"', false)
            ->assertInertia(fn ($page) => $page
                ->component('MultiCamera/Show')
                ->where('price', 5000)
                ->where('seo.canonicalUrl', $canonicalUrl)
                ->where('seo.ogImage', $ogImage)
                ->where('seo.noindex', false)
                ->has('coverages', 4)
                ->where('coverages.0.id', 'coverage-01')
                ->has('coverages.0.videos', 6)
                ->has('coverages.0.photos', 4)
                ->where('coverages.1.id', 'coverage-02')
                ->has('coverages.1.videos', 10)
                ->has('coverages.1.photos', 4)
                ->where('coverages.2.id', 'coverage-03')
                ->has('coverages.2.videos', 4)
                ->has('coverages.2.vertical_videos', 4)
                ->has('coverages.2.photos', 5)
                ->where('coverages.3.id', 'coverage-04')
                ->has('coverages.3.videos', 2)
                ->has('coverages.3.photos', 6)
                ->where('heroVideo.id', 'coverage-01-video-01')
                ->has('photos', 19)
                ->where('seo.jsonLd.@graph.4.@type', 'VideoObject')
                ->where('seo.jsonLd.@graph.5.@type', 'VideoObject')
                ->where('seo.jsonLd.@graph.6.@type', 'VideoObject'));

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee($canonicalUrl, false);
    }
}
