<?php

namespace Tests\Unit;

use App\Models\Dj;
use App\Models\Event;
use App\Models\PortfolioItem;
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

    public function test_booking_funnel_meta_uses_verified_portfolio_claim(): void
    {
        $settings = new SiteSetting([
            'booking_subtitle' => '1 reel + 10 fotos editadas',
            'booking_price' => 4000,
        ]);

        $meta = PageMeta::forBookingFunnel($settings, 'https://lapsique.media/');

        $this->assertSame('Más de 200 piezas audiovisuales producidas por Lapsique', $meta->title);
        $this->assertStringContainsString('Lapsique Media', $meta->metaTitle);
        $this->assertStringContainsString('archivo real', $meta->description);
        $this->assertStringContainsString('restaurantes', $meta->description);
        $this->assertStringNotContainsString('sesiones', mb_strtolower($meta->title.' '.$meta->description));
        $this->assertNotNull($meta->jsonLd);
        $service = collect($meta->jsonLd['@graph'])->firstWhere('@type', 'Service');
        $this->assertSame('Producción de reels para anuncios', $service['serviceType']);
        $this->assertSame('Riviera Maya', $service['areaServed']['name']);
        $this->assertSame(4000, $service['offers']['price']);
        $this->assertSame('https://lapsique.media/#agenda', $service['offers']['url']);
        $webPage = collect($meta->jsonLd['@graph'])->firstWhere('@type', 'WebPage');
        $this->assertSame($meta->title, $webPage['headline']);
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

    public function test_undated_event_uses_web_page_schema_instead_of_invalid_scheduled_event(): void
    {
        $event = Event::create([
            'title' => 'Archive Event Without Date',
            'slug' => 'archive-event-without-date',
            'venue' => 'Archive Venue',
            'trascendental_visible' => false,
        ]);

        $schema = PageMeta::forEvent($event, 'https://lapsique.media/eventos/archive-event-without-date')->jsonLd;

        $this->assertSame('WebPage', $schema['@type']);
        $this->assertArrayNotHasKey('startDate', $schema);
        $this->assertArrayNotHasKey('eventStatus', $schema);
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

        $this->assertSame('Grabación cinematográfica de DJ sets en Riviera Maya', $meta->title);
        $this->assertStringContainsString('Video continuo', $meta->description);
        $this->assertStringContainsString('fotografía editorial', $meta->description);
        $this->assertStringContainsString('images/og/djset.jpg', (string) $meta->ogImage);
        $this->assertStringNotContainsString('og-default.jpg', (string) $meta->ogImage);
    }

    public function test_djset_meta_uses_contextual_static_image_before_generic_portfolio(): void
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

        $this->assertStringContainsString('/images/portfolio/photos/067-fotos-proper-54490411c4.webp', (string) $meta->ogImage);
        $this->assertStringNotContainsString('/storage/', (string) $meta->ogImage);
        $this->assertStringNotContainsString('og-default.jpg', (string) $meta->ogImage);
    }

    public function test_djset_meta_keeps_contextual_static_image_when_featured_video_is_unrelated(): void
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

        $this->assertStringContainsString('/images/portfolio/photos/067-fotos-proper-54490411c4.webp', (string) $meta->ogImage);
        $this->assertStringNotContainsString('thumb', (string) $meta->ogImage);
        $this->assertStringNotContainsString('og-default.jpg', (string) $meta->ogImage);
    }

    public function test_drone_session_meta_includes_offer_and_static_media(): void
    {
        $meta = PageMeta::forDroneSession('https://lapsique.media/sesiones-de-dron');

        $this->assertSame('Vuelos con dron para propiedades y campañas en Riviera Maya', $meta->title);
        $this->assertStringContainsString('hoteles', $meta->description);
        $this->assertStringContainsString('Riviera Maya', $meta->description);
        $this->assertStringContainsString('/images/drone-sessions/hero.jpg', (string) $meta->ogImage);
        $graph = collect($meta->jsonLd['@graph']);
        $this->assertSame('Video y fotografía aérea con dron', $graph->firstWhere('@type', 'Service')['serviceType']);
        $this->assertSame('Vuelos con dron', $graph->firstWhere('@type', 'BreadcrumbList')['itemListElement'][1]['name']);
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

    public function test_electronic_event_coverage_meta_uses_the_real_mtrx_asset_and_fixed_offer(): void
    {
        $canonicalUrl = 'https://lapsique.media/cobertura-eventos-electronica';
        $meta = PageMeta::forElectronicEventCoverage($canonicalUrl);

        $this->assertSame('Cobertura de eventos de música electrónica en Riviera Maya', $meta->title);
        $this->assertStringContainsString('$4,500 MXN', $meta->description);
        $this->assertStringContainsString(
            '/images/portfolio/video-posters/2026-07-11-mtrx-dumas-a0794b89f7.jpg',
            (string) $meta->ogImage,
        );

        $graph = collect($meta->jsonLd['@graph']);
        $service = $graph->firstWhere('@type', 'Service');

        $this->assertSame('Cobertura audiovisual de eventos de música electrónica', $service['serviceType']);
        $this->assertSame(4500, $service['offers']['price']);
        $this->assertSame('MXN', $service['offers']['priceCurrency']);
        $this->assertSame($canonicalUrl, $service['offers']['url']);
        $this->assertCount(4, $graph->firstWhere('@type', 'FAQPage')['mainEntity']);
    }

    public function test_electronic_event_coverage_meta_is_localized_in_english(): void
    {
        app()->setLocale('en');

        $meta = PageMeta::forElectronicEventCoverage('https://lapsique.media/cobertura-eventos-electronica');
        $graph = collect($meta->jsonLd['@graph']);

        $this->assertSame('Electronic music event coverage in Riviera Maya', $meta->title);
        $this->assertSame(
            'Electronic music event audiovisual coverage',
            $graph->firstWhere('@type', 'Service')['serviceType'],
        );
        $this->assertSame(
            'What does event coverage include?',
            $graph->firstWhere('@type', 'FAQPage')['mainEntity'][0]['name'],
        );
    }

    public function test_food_reels_meta_includes_local_seo_schema_and_static_media(): void
    {
        $meta = PageMeta::forFoodReels(null, 'https://lapsique.media/reels-de-comida');

        $this->assertSame('Reels de comida para restaurantes en Riviera Maya', $meta->title);
        $this->assertStringContainsString('restaurantes', $meta->description);
        $this->assertStringContainsString('Cancún', $meta->description);
        $this->assertStringContainsString('/images/portfolio/photos/095-the-roof-comida-a715561b91.webp', (string) $meta->ogImage);
        $graph = collect($meta->jsonLd['@graph']);
        $this->assertSame('Producción audiovisual para restaurantes', $graph->firstWhere('@type', 'Service')['serviceType']);
        $this->assertSame('Reels de comida', $graph->firstWhere('@type', 'BreadcrumbList')['itemListElement'][1]['name']);
        $this->assertCount(4, $graph->firstWhere('@type', 'FAQPage')['mainEntity']);
    }

    public function test_content_creation_meta_targets_social_media_search_intent(): void
    {
        $meta = PageMeta::forContentCreation(null, 'https://lapsique.media/creacion-de-contenido-riviera-maya');

        $this->assertSame('Creación de contenido para redes en Riviera Maya', $meta->title);
        $this->assertStringContainsString('Instagram', $meta->description);
        $this->assertStringContainsString('TikTok', $meta->description);
        $this->assertStringContainsString('Meta Ads', $meta->description);
        $this->assertSame('https://lapsique.media/creacion-de-contenido-riviera-maya', $meta->canonicalUrl);
        $graph = collect($meta->jsonLd['@graph']);
        $this->assertSame('Creación de contenido para redes sociales', $graph->firstWhere('@type', 'Service')['serviceType']);
        $this->assertCount(4, $graph->firstWhere('@type', 'FAQPage')['mainEntity']);
    }

    public function test_business_reels_meta_targets_campaign_intent_and_has_no_empty_faq_schema(): void
    {
        $meta = PageMeta::forBusinessReels(null, 'https://lapsique.media/reels-para-negocios');
        $graph = collect($meta->jsonLd['@graph']);

        $this->assertSame('Reels para negocios y anuncios en Riviera Maya', $meta->title);
        $this->assertStringContainsString('hook, oferta y CTA', $meta->description);
        $this->assertStringContainsString('/images/portfolio/photos/063-dpm-ce73daedf9.webp', (string) $meta->ogImage);
        $this->assertSame('Producción de reels para negocios', $graph->firstWhere('@type', 'Service')['serviceType']);
        $this->assertNull($graph->firstWhere('@type', 'FAQPage'));
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
