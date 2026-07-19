<?php

namespace Tests\Feature;

use App\Models\Dj;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LapsiqueSiteIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_lapsique_pages_use_lapsique_meta_site_name(): void
    {
        config()->set('trascendental.enabled_as_primary', false);

        $this->get(route('portfolio.index'))
            ->assertOk()
            ->assertSee('data-inertia="author" name="author" content="Lapsique Media"', false)
            ->assertSee('data-inertia="og-site-name" property="og:site_name" content="Lapsique Media"', false);
    }

    public function test_lapsique_public_site_exposes_only_lapsique_djs(): void
    {
        config()->set('trascendental.enabled_as_primary', false);

        Dj::query()->create([
            'name' => 'Roster Artist',
            'slug' => 'roster-artist',
            'is_featured' => true,
            'trascendental_roster' => false,
        ]);

        Dj::query()->create([
            'name' => 'Trascendental Artist',
            'slug' => 'trascendental-artist',
            'trascendental_roster' => true,
        ]);

        $this->get(route('djs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Djs/Index')
                ->has('djs', 1)
                ->where('djs.0.name', 'Roster Artist'));
        $this->get(route('djs.show', ['dj' => 'roster-artist']))->assertOk();
        $this->get(route('djs.show', ['dj' => 'trascendental-artist']))->assertNotFound();
    }

    public function test_trascendental_primary_site_can_still_render_registered_djs(): void
    {
        config()->set('trascendental.enabled_as_primary', true);

        Dj::query()->create([
            'name' => 'Roster Artist',
            'slug' => 'roster-artist',
            'is_featured' => true,
            'trascendental_roster' => true,
        ]);

        Dj::query()->create([
            'name' => 'Lapsique Artist',
            'slug' => 'lapsique-artist',
            'trascendental_roster' => false,
        ]);

        $this->get(route('djs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Djs/Index')
                ->has('djs', 1)
                ->where('djs.0.name', 'Roster Artist'));
        $this->get(route('djs.show', ['dj' => 'lapsique-artist']))->assertNotFound();
    }
}
