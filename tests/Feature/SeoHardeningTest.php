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
}
