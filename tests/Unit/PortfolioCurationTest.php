<?php

namespace Tests\Unit;

use App\Models\PortfolioItem;
use App\Support\PortfolioCuration;
use Tests\TestCase;

class PortfolioCurationTest extends TestCase
{
    public function test_curates_preferred_items_before_generic_assets(): void
    {
        $items = collect([
            $this->item('portfolio-photo-048-yoga-merida', 1),
            $this->item('portfolio-photo-097-traumer-shonky', 90),
            $this->item('portfolio-photo-084-rebolledo', 80),
            $this->item('portfolio-photo-001-bioevolution', 2),
        ]);

        $curated = PortfolioCuration::curate(
            $items,
            ['traumer-shonky', 'rebolledo', 'bioevolution'],
            ['yoga-merida'],
            3,
        );

        $this->assertSame([
            'portfolio-photo-097-traumer-shonky',
            'portfolio-photo-084-rebolledo',
            'portfolio-photo-001-bioevolution',
        ], $curated->pluck('slug')->all());
    }

    public function test_curates_with_fallback_when_preferred_pool_is_short(): void
    {
        $items = collect([
            $this->item('portfolio-photo-097-traumer-shonky', 90),
            $this->item('portfolio-photo-042-the-roof-comida', 42),
            $this->item('portfolio-photo-011-juanis-barber-shop', 11),
        ]);

        $curated = PortfolioCuration::curate(
            $items,
            ['traumer-shonky'],
            ['yoga-merida'],
            3,
        );

        $this->assertSame([
            'portfolio-photo-097-traumer-shonky',
            'portfolio-photo-011-juanis-barber-shop',
            'portfolio-photo-042-the-roof-comida',
        ], $curated->pluck('slug')->all());
    }

    private function item(string $slug, int $priority): PortfolioItem
    {
        return new PortfolioItem([
            'title' => str($slug)->replace('-', ' ')->title()->toString(),
            'slug' => $slug,
            'type' => 'photo',
            'asset_path' => '/images/portfolio/photos/'.$slug.'.webp',
            'is_active' => true,
            'priority' => $priority,
        ]);
    }
}
