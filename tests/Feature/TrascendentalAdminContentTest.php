<?php

namespace Tests\Feature;

use App\Models\Dj;
use App\Models\Event;
use App\Models\PortfolioItem;
use App\Models\User;
use Database\Seeders\TrascendentalPublicContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrascendentalAdminContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TrascendentalPublicContentSeeder::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_public_content_admin_pages_render_with_path_based_assets(): void
    {
        $event = Event::query()
            ->where('slug', 'crihan-moonlight-2026')
            ->firstOrFail();

        $dj = Dj::query()
            ->where('slug', 'crihan')
            ->firstOrFail();

        $portfolioItem = PortfolioItem::query()->create([
            'title' => 'Trascendental Reel',
            'slug' => 'trascendental-reel',
            'type' => 'video',
            'orientation' => 'vertical',
            'source' => 'upload',
            'asset_path' => '/videos/trascendental/reel.mp4',
            'poster_path' => '/images/trascendental/events/moonlight-mamba-feed.webp',
            'is_active' => true,
            'priority' => 1,
        ]);

        $this->get(route('filament.admin.resources.events.index'))
            ->assertOk()
            ->assertSee('Eventos');

        $this->get(route('filament.admin.resources.events.edit', ['record' => $event]))
            ->assertOk()
            ->assertSee('Imagen pública existente');

        $this->get(route('filament.admin.resources.djs.index'))
            ->assertOk()
            ->assertSee('Djs');

        $this->get(route('filament.admin.resources.djs.edit', ['record' => $dj]))
            ->assertOk()
            ->assertSee('Imagen pública existente');

        $this->get(route('filament.admin.resources.portfolio-items.index'))
            ->assertOk()
            ->assertSee('Portafolio');

        $this->get(route('filament.admin.resources.portfolio-items.edit', ['record' => $portfolioItem]))
            ->assertOk()
            ->assertSee('Ruta pública del archivo')
            ->assertSee('Ruta pública del poster');
    }
}
