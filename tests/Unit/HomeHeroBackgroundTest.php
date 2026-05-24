<?php

namespace Tests\Unit;

use App\Models\PortfolioItem;
use App\Support\HomeHeroBackground;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeroBackgroundTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_null_when_no_usable_images(): void
    {
        $this->assertNull(HomeHeroBackground::resolve(collect()));
    }

    public function test_picks_background_from_youtube_portfolio_poster(): void
    {
        PortfolioItem::create([
            'title' => 'Aftermovie',
            'slug' => 'aftermovie',
            'type' => 'video',
            'source' => 'youtube',
            'youtube_id' => 'abc12345678',
            'youtube_url' => 'https://www.youtube.com/watch?v=abc12345678',
            'is_active' => true,
            'is_featured' => true,
            'priority' => 1,
        ]);

        $background = HomeHeroBackground::resolve(PortfolioItem::query()->get());

        $this->assertNotNull($background);
        $this->assertSame('Aftermovie', $background['alt']);
        $this->assertStringContainsString('abc12345678', $background['url']);
    }

    public function test_randomizes_among_multiple_candidates(): void
    {
        foreach (['clip-a', 'clip-b', 'clip-c'] as $index => $slug) {
            PortfolioItem::create([
                'title' => "Clip {$index}",
                'slug' => $slug,
                'type' => 'video',
                'source' => 'youtube',
                'youtube_id' => "id0000000{$index}",
                'youtube_url' => "https://www.youtube.com/watch?v=id0000000{$index}",
                'is_active' => true,
                'priority' => $index,
            ]);
        }

        $urls = collect(range(1, 12))
            ->map(fn () => HomeHeroBackground::resolve(PortfolioItem::query()->get())['url'] ?? null)
            ->filter()
            ->unique();

        $this->assertGreaterThanOrEqual(2, $urls->count());
    }
}
