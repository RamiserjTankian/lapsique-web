<?php

namespace Tests\Unit;

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

    public function test_booking_funnel_meta_includes_price_and_deliverables(): void
    {
        $settings = new SiteSetting([
            'booking_subtitle' => '1 reel + 10 fotos editadas',
            'booking_price' => 4000,
        ]);

        $meta = PageMeta::forBookingFunnel($settings, 'https://lapsique.media/');

        $this->assertSame('Reels para anuncios de tu negocio', $meta->title);
        $this->assertStringContainsString('Lapsique Media', $meta->metaTitle);
        $this->assertStringContainsString('Meta Ads', $meta->description);
        $this->assertStringContainsString('dron DJI', $meta->description);
        $this->assertStringContainsString('4,000', $meta->description);
        $this->assertNotNull($meta->jsonLd);
        $this->assertSame('Producción de reels para anuncios', $meta->jsonLd['serviceType']);
        $this->assertSame('Riviera Maya', $meta->jsonLd['areaServed']['name']);
        $this->assertSame(4000, $meta->jsonLd['offers']['price']);
        $this->assertSame('https://lapsique.media/#agenda', $meta->jsonLd['offers']['url']);
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

        $this->assertSame('Sesiones de vuelo con dron', $meta->title);
        $this->assertStringContainsString('DJI Air 3', $meta->description);
        $this->assertStringContainsString('3,000', $meta->description);
        $this->assertStringContainsString('Cancún', $meta->description);
        $this->assertStringContainsString('/images/drone-sessions/hero.jpg', (string) $meta->ogImage);
        $this->assertSame('Vuelo con dron DJI Air 3', $meta->jsonLd['serviceType']);
        $this->assertSame(3000, $meta->jsonLd['offers']['price']);
        $this->assertSame('https://lapsique.media/sesiones-de-dron#agenda', $meta->jsonLd['offers']['url']);
    }

    public function test_construction_progress_meta_includes_offer_and_static_media(): void
    {
        $meta = PageMeta::forConstructionProgress('https://lapsique.media/avances-de-obra');

        $this->assertSame('Avances de obra con dron', $meta->title);
        $this->assertStringContainsString('DJI Air 3', $meta->description);
        $this->assertStringContainsString('5,000', $meta->description);
        $this->assertStringContainsString('Cancún', $meta->description);
        $this->assertStringContainsString('/images/drone-sessions/construction-goba-aerial.jpg', (string) $meta->ogImage);
        $this->assertSame('Avance de obra con dron DJI Air 3', $meta->jsonLd['serviceType']);
        $this->assertSame(5000, $meta->jsonLd['offers']['price']);
        $this->assertSame('https://lapsique.media/avances-de-obra#agenda', $meta->jsonLd['offers']['url']);
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
