<?php

namespace Tests\Unit;

use App\Models\PortfolioItem;
use App\Models\SiteSetting;
use App\Support\PageMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_funnel_meta_includes_price_and_deliverables(): void
    {
        $settings = new SiteSetting([
            'booking_subtitle' => '1 reel + 10 fotos editadas',
            'booking_price' => 3000,
        ]);

        $meta = PageMeta::forBookingFunnel($settings, 'https://lapsique.media/');

        $this->assertSame('Agenda reels para tu negocio', $meta->title);
        $this->assertStringContainsString('lapsique.media', $meta->metaTitle);
        $this->assertStringContainsString('1 reel + 10 fotos editadas', $meta->description);
        $this->assertStringContainsString('3,000', $meta->description);
        $this->assertNotNull($meta->jsonLd);
        $this->assertStringContainsString('booking-og.jpg', (string) $meta->ogImage);
    }

    public function test_booking_og_image_prefers_admin_upload(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/og/custom.jpg', 'x');

        $settings = new SiteSetting([
            'booking_og_image' => 'images/og/custom.jpg',
            'booking_price' => 3000,
        ]);

        $meta = PageMeta::forBookingFunnel($settings, 'https://lapsique.media/');

        $this->assertStringContainsString('images/og/custom.jpg', (string) $meta->ogImage);
    }

    public function test_booking_og_image_uses_active_portfolio_photo_before_static_fallback(): void
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

        $this->assertStringContainsString('portfolio', (string) $meta->ogImage);
        $this->assertStringNotContainsString('booking-og.jpg', (string) $meta->ogImage);
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
