<?php

namespace Tests\Feature;

use App\Filament\Resources\Djs\Pages\EditDj;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\PageContentBlocks\Pages\CreatePageContentBlock;
use App\Filament\Resources\PageContentBlocks\Pages\EditPageContentBlock;
use App\Filament\Resources\PortfolioItems\Pages\EditPortfolioItem;
use App\Models\Dj;
use App\Models\Event;
use App\Models\PageContentBlock;
use App\Models\PortfolioItem;
use App\Models\User;
use Database\Seeders\TrascendentalPublicContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class TrascendentalAdminContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ini_set('memory_limit', '512M');

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

    public function test_public_content_records_can_be_saved_from_filament_with_existing_asset_paths(): void
    {
        $event = Event::query()
            ->where('slug', 'crihan-moonlight-2026')
            ->firstOrFail();

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->fillForm([
                'starts_at' => null,
                'venue' => '@zolua updated',
                'public_image_path' => '/images/trascendental/events/crihan-moonlight-2026.webp',
                'trascendental_visible' => true,
                'trascendental_kind' => 'roster_appearance',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'starts_at' => null,
            'venue' => '@zolua updated',
            'public_image_path' => '/images/trascendental/events/crihan-moonlight-2026.webp',
        ]);

        $dj = Dj::query()
            ->where('slug', 'crihan')
            ->firstOrFail();

        Livewire::test(EditDj::class, ['record' => $dj->getRouteKey()])
            ->fillForm([
                'booking_status' => 'LAST DATES',
                'public_image_path' => '/images/trascendental/artists/crihan-portrait.jpeg',
                'trascendental_roster' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('djs', [
            'id' => $dj->id,
            'booking_status' => 'LAST DATES',
            'public_image_path' => '/images/trascendental/artists/crihan-portrait.jpeg',
        ]);

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

        Livewire::test(EditPortfolioItem::class, ['record' => $portfolioItem->getRouteKey()])
            ->fillForm([
                'title' => 'Trascendental Reel Updated',
                'asset_path' => '/videos/trascendental/reel-updated.mp4',
                'poster_path' => '/images/trascendental/events/moonlight-mamba-feed.webp',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('portfolio_items', [
            'id' => $portfolioItem->id,
            'title' => 'Trascendental Reel Updated',
            'asset_path' => '/videos/trascendental/reel-updated.mp4',
        ]);
    }

    public function test_public_content_media_upload_fields_can_be_saved_from_filament(): void
    {
        $event = Event::query()
            ->where('slug', 'crihan-insight-2026')
            ->firstOrFail();

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->fillForm([
                'cover_vertical' => UploadedFile::fake()->image('crihan-flyer.jpg', 320, 420),
                'has_vertical_poster' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($event->fresh()->hasMedia('cover_vertical'));

        $dj = Dj::query()
            ->where('slug', 'crihan')
            ->firstOrFail();

        Livewire::test(EditDj::class, ['record' => $dj->getRouteKey()])
            ->fillForm([
                'profile' => UploadedFile::fake()->image('crihan-profile.jpg', 420, 240),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($dj->fresh()->hasMedia('profile'));
    }

    public function test_editorial_blocks_can_be_created_and_updated_from_filament(): void
    {
        Livewire::test(CreatePageContentBlock::class)
            ->fillForm([
                'site' => 'trascendental',
                'locale' => 'en',
                'page' => 'home',
                'section' => 'hero',
                'key' => 'headline',
                'title' => 'Events. Artists. Culture.',
                'body' => 'Editable from Filament.',
                'asset_path' => '/videos/trascendental/home.mp4',
                'cta_label' => 'Start a project',
                'cta_url' => '/contacto',
                'is_active' => true,
                'priority' => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $block = PageContentBlock::query()
            ->where('site', 'trascendental')
            ->where('page', 'home')
            ->where('section', 'hero')
            ->where('key', 'headline')
            ->firstOrFail();

        Livewire::test(EditPageContentBlock::class, ['record' => $block->getKey()])
            ->fillForm([
                'title' => 'Events. Artists. Culture. Updated',
                'asset_path' => '/videos/trascendental/home-updated.mp4',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('page_content_blocks', [
            'id' => $block->id,
            'title' => 'Events. Artists. Culture. Updated',
            'asset_path' => '/videos/trascendental/home-updated.mp4',
        ]);
    }
}
