<?php

namespace App\Support;

use App\Models\PortfolioItem;
use Illuminate\Support\Collection;

class PortfolioCuration
{
    private const HOME_PATTERNS = [
        'traumer-shonky',
        'rebolledo',
        'proper-collective',
        'santino-on-heaven',
        'santino-22-de-marzo',
        'on-heaven',
        'umi',
        'dron-malfa',
        'fotos-proper',
        'the-roof-comida',
        'bioevolution',
        'dpm',
        'zal-marina',
        'juanis-barber-shop',
    ];

    private const HOME_EXCLUDED_PATTERNS = [
        'yoga-merida',
        'michelle',
        'valentina',
        'lucia',
        'olga-korol',
        'malen',
        'teo',
        'tanuki',
        'fernando-praga',
    ];

    private const DJ_SET_PATTERNS = [
        'traumer-shonky',
        'rebolledo',
        'santino-on-heaven',
        'santino-22-de-marzo',
        'on-heaven',
        'proper-collective',
        'umi',
        'dron-malfa',
        'fotos-proper',
    ];

    private const DJ_SET_EXCLUDED_PATTERNS = [
        'bioevolution',
        'dpm',
        'juanis',
        'barber',
        'the-roof',
        'comida',
        'yoga',
        'zal-marina',
        'michelle',
        'valentina',
        'lucia',
        'olga',
        'fernando-praga',
        'tanuki',
    ];

    private const SCENE_PATTERNS = [
        'traumer-shonky',
        'rebolledo',
        'proper-collective',
        'satoshi',
        'vatos-locos',
        'sudbeat',
        'sasha',
        'victor-ruiz',
        'basement',
        'pergola',
        'umi',
        'on-heaven',
        'santino',
    ];

    /**
     * @return Collection<int, PortfolioItem>
     */
    public static function forHome(int $limit = 12): Collection
    {
        return self::curate(
            self::basePhotoQuery()->get(),
            self::HOME_PATTERNS,
            self::HOME_EXCLUDED_PATTERNS,
            $limit,
        );
    }

    /**
     * @return Collection<int, PortfolioItem>
     */
    public static function forDjSet(int $limit = 10): Collection
    {
        return self::curate(
            self::basePhotoQuery()->get(),
            self::DJ_SET_PATTERNS,
            self::DJ_SET_EXCLUDED_PATTERNS,
            $limit,
        );
    }

    /**
     * @return Collection<int, PortfolioItem>
     */
    public static function forScene(int $limit = 12): Collection
    {
        return self::curate(
            self::basePublicQuery()->whereIn('type', ['photo', 'reel', 'aftermovie', 'video'])->get(),
            self::SCENE_PATTERNS,
            self::DJ_SET_EXCLUDED_PATTERNS,
            $limit,
        );
    }

    /**
     * @return Collection<int, PortfolioItem>
     */
    public static function forAftermovies(int $limit = 12): Collection
    {
        return self::curate(
            self::basePublicQuery()->where('type', 'aftermovie')->get(),
            self::SCENE_PATTERNS,
            [],
            $limit,
        );
    }

    /**
     * @param  Collection<int, PortfolioItem>  $items
     * @param  list<string>  $preferredPatterns
     * @param  list<string>  $excludedPatterns
     * @return Collection<int, PortfolioItem>
     */
    public static function curate(Collection $items, array $preferredPatterns, array $excludedPatterns, int $limit): Collection
    {
        $usable = $items
            ->filter(fn (PortfolioItem $item): bool => self::hasPublicMedia($item))
            ->values();

        if ($usable->isEmpty()) {
            return collect();
        }

        $preferred = $usable
            ->reject(fn (PortfolioItem $item): bool => self::matchesAny($item, $excludedPatterns))
            ->sortBy([
                fn (PortfolioItem $a, PortfolioItem $b): int => self::patternRank($a, $preferredPatterns) <=> self::patternRank($b, $preferredPatterns),
                fn (PortfolioItem $a, PortfolioItem $b): int => ($b->is_featured <=> $a->is_featured),
                fn (PortfolioItem $a, PortfolioItem $b): int => ($a->priority <=> $b->priority),
                fn (PortfolioItem $a, PortfolioItem $b): int => ($b->id <=> $a->id),
            ])
            ->take($limit);

        if ($preferred->count() >= $limit) {
            return $preferred->values();
        }

        return $preferred
            ->merge($usable->whereNotIn('id', $preferred->pluck('id'))->take($limit - $preferred->count()))
            ->values();
    }

    protected static function basePhotoQuery()
    {
        return self::basePublicQuery()
            ->where('type', 'photo');
    }

    protected static function basePublicQuery()
    {
        return PortfolioItem::query()
            ->where('is_active', true)
            ->with('media')
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->orderByDesc('created_at');
    }

    public static function hasPublicMedia(PortfolioItem $item): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        foreach ([$item->asset_path, $item->poster_path] as $path) {
            if (filled($path) && is_readable(public_path(ltrim((string) $path, '/')))) {
                return true;
            }
        }

        foreach (['asset', 'poster'] as $collection) {
            $media = $item->getFirstMedia($collection);

            if ($media && is_readable($media->getPath())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $patterns
     */
    private static function patternRank(PortfolioItem $item, array $patterns): int
    {
        $haystack = self::haystack($item);

        foreach ($patterns as $index => $pattern) {
            if (str_contains($haystack, $pattern)) {
                return $index;
            }
        }

        return 999;
    }

    /**
     * @param  list<string>  $patterns
     */
    private static function matchesAny(PortfolioItem $item, array $patterns): bool
    {
        $haystack = self::haystack($item);

        foreach ($patterns as $pattern) {
            if (str_contains($haystack, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private static function haystack(PortfolioItem $item): string
    {
        return str($item->type.' '.$item->slug.' '.$item->title.' '.$item->asset_path.' '.$item->poster_path.' '.implode(' ', $item->tags ?? []))
            ->lower()
            ->toString();
    }
}
