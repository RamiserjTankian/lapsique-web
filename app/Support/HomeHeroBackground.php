<?php

namespace App\Support;

use App\Models\PortfolioItem;
use Illuminate\Support\Collection;

class HomeHeroBackground
{
    /**
     * @param  Collection<int, PortfolioItem>  $portfolioItems
     * @return array{url: string, alt: string|null}|null
     */
    public static function resolve(Collection $portfolioItems): ?array
    {
        $candidates = $portfolioItems
            ->map(fn (PortfolioItem $item) => self::forItem($item))
            ->filter()
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        return $candidates->random();
    }

    /**
     * @return array{url: string, alt: string|null}|null
     */
    protected static function forItem(PortfolioItem $item): ?array
    {
        $url = self::imageUrl($item);

        if ($url === null) {
            return null;
        }

        return [
            'url' => $url,
            'alt' => $item->title,
        ];
    }

    protected static function imageUrl(PortfolioItem $item): ?string
    {
        $assetMedia = $item->getFirstMedia('asset');
        $isUploadVideo = $assetMedia && str_starts_with((string) $assetMedia->mime_type, 'video/');

        $url = match (true) {
            $item->source === 'youtube' => $item->poster_url,
            $isUploadVideo => $item->poster_url,
            default => $item->asset_url ?: $item->poster_url,
        };

        if (blank($url) || self::isPlaceholderUrl($url)) {
            return null;
        }

        return $url;
    }

    protected static function isPlaceholderUrl(string $url): bool
    {
        return str_contains($url, 'og-default.jpg');
    }
}
