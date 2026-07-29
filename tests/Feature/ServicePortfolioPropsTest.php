<?php

namespace Tests\Feature;

use App\Models\Dj;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePortfolioPropsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('trascendental.enabled_as_primary', false);
    }

    public function test_home_exposes_portfolio_overview_without_presenting_media_as_sessions(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->where('portfolioOverview.site', 'lapsique')
                ->where('portfolioOverview.archiveMediaCount', 220)
                ->has('portfolioOverview.projects', 8)
                ->has('portfolioOverview.featuredMedia', 14)
                ->has('portfolioOverview.servicePreviews', 8)
                ->missing('portfolioOverview.sessionCount'));
    }

    public function test_each_service_landing_exposes_its_own_catalog_bundle(): void
    {
        foreach ([
            'content-creation.show' => ['ContentCreation/Show', 'content_creation'],
            'business-reels.show' => ['ContentCreation/Show', 'business_reels'],
            'food-reels.show' => ['FoodReels/Show', 'food_reels'],
            'djset.show' => ['DjSet/Show', 'dj_set'],
            'electronic-event-coverage.show' => ['EventCoverage/Show', 'event_coverage'],
            'multi-camera.show' => ['MultiCamera/Show', 'multi_camera'],
            'drone-sessions.show' => ['DroneSessions/Show', 'drone_sessions'],
            'construction-progress.show' => ['ConstructionProgress/Show', 'construction_progress'],
        ] as $route => [$component, $serviceKey]) {
            $this->get(route($route))
                ->assertOk()
                ->assertInertia(fn ($page) => $page
                    ->component($component)
                    ->where('servicePortfolio.serviceKey', $serviceKey)
                    ->where('servicePortfolio.hero.site', 'lapsique')
                    ->has('servicePortfolio.projects')
                    ->has('servicePortfolio.stats.mediaCount')
                    ->has('servicePortfolio.stats.projectCount'));
        }
    }

    public function test_english_service_landings_receive_localized_catalog_copy(): void
    {
        $this->withCookie('locale', 'en')
            ->get(route('business-reels.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('servicePortfolio.hero.projectLabel', 'Padel campaign')
                ->where('servicePortfolio.hero.sessionLabel', 'Hook · problem')
                ->where('servicePortfolio.hero.alt', 'Padel ad reel built around a customer problem.')
                ->where('servicePortfolio.projects.0.label', 'Padel campaign'));

        $this->withCookie('locale', 'en')
            ->get(route('construction-progress.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('servicePortfolio.hero.projectLabel', 'GOBA')
                ->where('servicePortfolio.hero.sessionLabel', 'GOBA · recent progress')
                ->where('servicePortfolio.hero.alt', 'Contextual aerial view of GOBA construction progress.')
                ->where('servicePortfolio.projects.0.label', 'GOBA · recent progress'));
    }

    public function test_dj_set_excludes_trascendental_and_mixed_roster_records(): void
    {
        $lapsiqueDj = Dj::query()->create([
            'name' => 'Lapsique Artist',
            'slug' => 'lapsique-artist',
            'trascendental_roster' => false,
            'is_featured' => true,
        ]);
        $trascendentalDj = Dj::query()->create([
            'name' => 'Trascendental Artist',
            'slug' => 'trascendental-artist',
            'trascendental_roster' => true,
            'is_highlighted' => true,
        ]);
        $lapsiqueVideo = Video::query()->create([
            'title' => 'Lapsique Original',
            'slug' => 'lapsique-original',
            'youtube_id' => 'lapsique001',
            'youtube_url' => 'https://www.youtube.com/watch?v=lapsique001',
            'tags' => ['psique-originals'],
            'is_featured' => true,
        ]);
        $trascendentalVideo = Video::query()->create([
            'title' => 'Trascendental Original',
            'slug' => 'trascendental-original',
            'youtube_id' => 'trascend01',
            'youtube_url' => 'https://www.youtube.com/watch?v=trascend01',
            'tags' => ['psique-originals'],
            'is_featured' => true,
        ]);
        $mixedVideo = Video::query()->create([
            'title' => 'Mixed Original',
            'slug' => 'mixed-original',
            'youtube_id' => 'mixed000001',
            'youtube_url' => 'https://www.youtube.com/watch?v=mixed000001',
            'tags' => ['psique-originals'],
            'is_featured' => true,
        ]);
        $lapsiqueVideo->djs()->attach($lapsiqueDj);
        $trascendentalVideo->djs()->attach($trascendentalDj);
        $mixedVideo->djs()->attach([$lapsiqueDj->id, $trascendentalDj->id]);

        $this->get(route('djset.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('originals', 1)
                ->where('originals.0.title', 'Lapsique Original')
                ->has('djs', 1)
                ->where('djs.0.name', 'Lapsique Artist'));
    }
}
