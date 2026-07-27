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
            ->assertInertia(fn ($page) => $page
                ->component('ContentCreation/Show')
                ->where('seo.canonicalUrl', route('content-creation.show'))
                ->where('seo.noindex', false));

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('content-creation.show'), false);
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

        $this->get($canonicalUrl)
            ->assertOk()
            ->assertSee('data-inertia="canonical" rel="canonical" href="'.$canonicalUrl.'"', false)
            ->assertInertia(fn ($page) => $page
                ->component('MultiCamera/Show')
                ->where('price', 5000)
                ->where('seo.canonicalUrl', $canonicalUrl)
                ->where('seo.noindex', false)
                ->has('drops', 10)
                ->where('drops.0.orientation', 'horizontal')
                ->where('drops.2.orientation', 'vertical')
                ->has('photos', 15));

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee($canonicalUrl, false);
    }
}
