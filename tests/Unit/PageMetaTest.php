<?php

namespace Tests\Unit;

use App\Models\PortfolioItem;
use App\Models\Dj;
use App\Models\Event;
use App\Models\SiteSetting;
use App\Models\Video;
use App\Support\PageMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageMetaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('es');
    }

    public function test_booking_funnel_meta_includes_price_and_deliverables(): void
    {
        $settings = new SiteSetting([
            'booking_subtitle' => '1 reel + 10 fotos editadas',
            'booking_price' => 4000,
        ]);

        $meta = PageMeta::forBookingFunnel($settings, 'https://lapsique.media/');

        $this->assertSame('Producción audiovisual, DJ sets y eventos en Riviera Maya', $meta->title);
        $this->assertStringContainsString('Lapsique Media', $meta->metaTitle);
        $this->assertStringContainsString('Psique Sessions', $meta->description);
        $this->assertStringContainsString('Sony Alpha', $meta->description);
        $this->assertStringContainsString('4,000', $meta->description);
        $this->assertNotNull($meta->jsonLd);
        $service = collect($meta->jsonLd['@graph'])->firstWhere('@type', 'Service');
        $this->assertSame('Producción de reels para anuncios', $service['serviceType']);
        $this->assertSame('Riviera Maya', $service['areaServed']['name']);
        $this->assertSame(4000, $service['offers']['price']);
        $this->assertSame('https://lapsique.media/#agenda', $service['offers']['url']);
        $this->assertStringContainsString('booking-og.jpg', (string) $meta->ogImage);
    }

    public function test_booking_og_image_prefers_admin_upload(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/og/custom.jpg', 'x');

        $settings = new SiteSetting([
            'booking_og_image' => 'images/og/custom.jpg',
            'booking_price' => 4000,
        ]);

        $meta = PageMeta::forBookingFunnel($settings, 'https://lapsique.media/');

        $this->assertStringContainsString('images/og/custom.jpg', (string) $meta->ogImage);
    }

    public function test_editorial_entities_publish_specific_schema_types(): void
    {
        $dj = Dj::create([
            'name' => 'Schema Artist',
            'slug' => 'schema-artist',
            'bio' => 'Electronic artist documented by Lapsique Media.',
            'trascendental_roster' => false,
        ]);
        $video = Video::create([
            'title' => 'Schema Session',
            'slug' => 'schema-session',
            'youtube_id' => 'abcdefghijk',
            'youtube_url' => 'https://www.youtube.com/watch?v=abcdefghijk',
            'thumbnail_url' => 'https://img.youtube.com/vi/abcdefghijk/maxresdefault.jpg',
            'published_at' => now(),
        ]);
        $event = Event::create([
            'title' => 'Schema Event',
            'slug' => 'schema-event',
            'starts_at' => now()->addMonth(),
            'venue' => 'Test Venue',
            'city' => 'Tulum',
            'trascendental_visible' => false,
        ]);

        $this->assertSame('Person', PageMeta::forDj($dj, 'https://lapsique.media/djs/schema-artist')->jsonLd['@type']);
        $this->assertSame('VideoObject', PageMeta::forVideo($video, 'https://lapsique.media/trabajos-en-video/schema-session')->jsonLd['@type']);
        $this->assertSame('Event', PageMeta::forEvent($event, 'https://lapsique.media/eventos/schema-event')->jsonLd['@type']);
    }

    public function test_booking_og_image_uses_static_campaign_image_before_portfolio_photo(): void
    {
        Storage::fake('public');

        $item = PortfolioItem::create([
            'title' => 'Gallery portrait',
            'slug' => 'gallery-portrait',
            'type' => 'photo',
            'is_active' => true,
            'is_featured' => true,
            'priority' => 1,
        ]);
        $item->addMedia(UploadedFile::fake()->image('portfolio.jpg', 1200, 800))
            ->toMediaCollection('asset');

        $meta = PageMeta::forBookingFunnel(null, 'https://lapsique.media/');

        $this->assertStringContainsString('booking-og.jpg', (string) $meta->ogImage);
        $this->assertStringNotContainsString('portfolio', (string) $meta->ogImage);
    }

    public function test_booking_og_image_falls_back_when_portfolio_file_is_missing(): void
    {
        Storage::fake('public');

        $item = PortfolioItem::create([
            'title' => 'Missing file portrait',
            'slug' => 'missing-file-portrait',
            'type' => 'photo',
            'is_active' => true,
            'is_featured' => true,
            'priority' => 1,
        ]);
        $media = $item->addMedia(UploadedFile::fake()->image('portfolio.jpg', 1200, 800))
            ->toMediaCollection('asset');

        if ($media->hasGeneratedConversion('large')) {
            @unlink($media->getPath('large'));
        }
        @unlink($media->getPath());

        $meta = PageMeta::forBookingFunnel(null, 'https://lapsique.media/');

        $this->assertStringContainsString('booking-og.jpg', (string) $meta->ogImage);
    }

    public function test_djset_meta_uses_admin_upload(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/og/djset.jpg', 'x');

        $settings = new SiteSetting([
            'djset_og_image' => 'images/og/djset.jpg',
        ]);

        $meta = PageMeta::forDjSet($settings, 'https://lapsique.media/dj-set');

        $this->assertSame('Grabación de DJ Set', $meta->title);
        $this->assertStringContainsString('10,000', $meta->description);
        $this->assertStringContainsString('Ronin', $meta->description);
        $this->assertStringContainsString('images/og/djset.jpg', (string) $meta->ogImage);
        $this->assertStringNotContainsString('og-default.jpg', (string) $meta->ogImage);
    }

    public function test_djset_meta_uses_portfolio_before_default_og(): void
    {
        Storage::fake('public');

        $item = PortfolioItem::create([
            'title' => 'DJ night',
            'slug' => 'dj-night',
            'type' => 'photo',
            'is_active' => true,
            'is_featured' => true,
            'priority' => 1,
        ]);
        $item->addMedia(UploadedFile::fake()->image('dj.jpg', 1200, 630))
            ->toMediaCollection('asset');

        $meta = PageMeta::forDjSet(null, 'https://lapsique.media/dj-set');

        $this->assertStringContainsString('/storage/', (string) $meta->ogImage);
        $this->assertStringNotContainsString('og-default.jpg', (string) $meta->ogImage);
    }

    public function test_djset_meta_uses_featured_video_thumbnail_when_no_portfolio_photo(): void
    {
        Storage::fake('public');

        $video = Video::create([
            'title' => 'Set en vivo',
            'slug' => 'set-en-vivo',
            'youtube_id' => 'djset-thumb-test',
            'youtube_url' => 'https://youtube.test/djset-thumb',
            'is_featured' => true,
            'priority' => 1,
        ]);
        $video->addMedia(UploadedFile::fake()->image('thumb.jpg', 1280, 720))
            ->toMediaCollection('thumbnail');

        $meta = PageMeta::forDjSet(null, 'https://lapsique.media/dj-set');

        $this->assertStringContainsString('thumb', (string) $meta->ogImage);
        $this->assertStringNotContainsString('og-default.jpg', (string) $meta->ogImage);
    }

    public function test_drone_session_meta_includes_offer_and_static_media(): void
    {
        $meta = PageMeta::forDroneSession('https://lapsique.media/sesiones-de-dron');

        $this->assertSame('Sesiones de dron en Riviera Maya para hoteles y propiedades', $meta->title);
        $this->assertStringContainsString('hoteles', $meta->description);
        $this->assertStringContainsString('Riviera Maya', $meta->description);
        $this->assertStringContainsString('/images/drone-sessions/hero.jpg', (string) $meta->ogImage);
        $graph = collect($meta->jsonLd['@graph']);
        $this->assertSame('Video y fotografía aérea con dron', $graph->firstWhere('@type', 'Service')['serviceType']);
        $this->assertSame('Sesiones de dron', $graph->firstWhere('@type', 'BreadcrumbList')['itemListElement'][1]['name']);
        $this->assertCount(4, $graph->firstWhere('@type', 'FAQPage')['mainEntity']);
    }

    public function test_construction_progress_meta_includes_offer_and_static_media(): void
    {
        $meta = PageMeta::forConstructionProgress('https://lapsique.media/avances-de-obra');

        $this->assertSame('Avances de obra con dron, foto y video en Riviera Maya', $meta->title);
        $this->assertStringContainsString('constructoras', $meta->description);
        $this->assertStringContainsString('Riviera Maya', $meta->description);
        $this->assertStringContainsString('/images/drone-sessions/construction-goba-aerial.jpg', (string) $meta->ogImage);
        $graph = collect($meta->jsonLd['@graph']);
        $this->assertSame('Documentación audiovisual de construcción', $graph->firstWhere('@type', 'Service')['serviceType']);
        $this->assertSame('Avances de obra', $graph->firstWhere('@type', 'BreadcrumbList')['itemListElement'][1]['name']);
        $this->assertCount(4, $graph->firstWhere('@type', 'FAQPage')['mainEntity']);
    }

    public function test_food_reels_meta_includes_local_seo_schema_and_static_media(): void
    {
        $meta = PageMeta::forFoodReels(null, 'https://lapsique.media/reels-de-comida');

        $this->assertSame('Reels de comida para restaurantes en Riviera Maya', $meta->title);
        $this->assertStringContainsString('restaurantes', $meta->description);
        $this->assertStringContainsString('Cancún', $meta->description);
        $this->assertStringContainsString('/images/food-reels/sushiclub-day-sushi-promo-poster.jpg', (string) $meta->ogImage);
        $graph = collect($meta->jsonLd['@graph']);
        $this->assertSame('Producción audiovisual para restaurantes', $graph->firstWhere('@type', 'Service')['serviceType']);
        $this->assertSame('Reels de comida', $graph->firstWhere('@type', 'BreadcrumbList')['itemListElement'][1]['name']);
        $this->assertCount(4, $graph->firstWhere('@type', 'FAQPage')['mainEntity']);
    }

    public function test_booking_status_meta_is_noindex(): void
    {
        $meta = PageMeta::forBookingStatus(
            'Reserva confirmada',
            'Detalle de reserva.',
            'https://lapsique.media/sesion-de-contenido/abc/confirm',
        );

        $this->assertTrue($meta->noindex);
    }
}
