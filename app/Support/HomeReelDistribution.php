<?php

namespace App\Support;

class HomeReelDistribution
{
    /**
     * @return array{
     *     landingVideos: array{
     *         hero: array{src: string, poster: string|null, title: string|null}|null,
     *         offer: array{src: string, poster: string|null, title: string|null}|null,
     *         proof: array{src: string, poster: string|null, title: string|null}|null,
     *         pauta: array{src: string, poster: string|null, title: string|null}|null,
     *         creative: list<array{src: string, poster: string|null, title: string|null}>,
     *         equipment: list<array{src: string, poster: string|null, title: string|null}>,
     *         aftermovies: list<array{src: string, poster: string|null, title: string|null}>,
     *         floats: list<array{src: string, poster: string|null, title: string|null}>,
     *         package: array{src: string, poster: string|null, title: string|null}|null,
     *         gear: array{src: string, poster: string|null, title: string|null}|null,
     *     },
     *     heroProofVideo: array{
     *         title: string|null,
     *         media_type: 'video',
     *         embed_url: null,
     *         playback_url: string,
     *         poster_url: string|null,
     *     }|null,
     *     reelLibraryPreview: list<array{id: string, src: string, poster: string|null}>,
     *     reelStats: array{totalSourceVideos: int, uniqueVideos: int},
     * }|null
     */
    public static function forHome(?int $previewCount = null): ?array
    {
        $pool = ReelLibrary::all();

        if ($pool === []) {
            return null;
        }

        shuffle($pool);

        $previewCount = $previewCount ?? max(1, (int) config('landing.reels_library_preview_count', 10));
        $landingSlotCount = 17;
        $totalSlots = $landingSlotCount + $previewCount;
        $picks = self::pickEntries($pool, $totalSlots);

        $index = 0;
        $hero = self::pickEntryAt($picks, $index++);
        $offer = self::pickEntryAt($picks, $index++);
        $proof = self::pickEntryAt($picks, $index++);
        $pauta = self::pickEntryAt($picks, $index++);
        $creative = self::compactEntries([
            self::pickEntryAt($picks, $index++),
            self::pickEntryAt($picks, $index++),
            self::pickEntryAt($picks, $index++),
        ]);
        $equipment = self::compactEntries([
            self::pickEntryAt($picks, $index++),
            self::pickEntryAt($picks, $index++),
        ]);
        $aftermovies = self::compactEntries([
            self::pickEntryAt($picks, $index++),
            self::pickEntryAt($picks, $index++),
            self::pickEntryAt($picks, $index++),
            self::pickEntryAt($picks, $index++),
        ]);
        $floats = self::compactEntries([
            self::pickEntryAt($picks, $index++),
            self::pickEntryAt($picks, $index++),
        ]);
        $package = self::pickEntryAt($picks, $index++);
        $gear = self::pickEntryAt($picks, $index++);

        $previewEntries = array_slice($picks, $index, $previewCount);
        $reelLibraryPreview = array_map(
            fn (array $entry) => [
                'id' => $entry['id'],
                'src' => $entry['src'],
                'poster' => self::posterForSrc($entry['src']),
            ],
            $previewEntries,
        );

        $landingVideos = [
            'hero' => $hero,
            'offer' => $offer,
            'proof' => $proof,
            'pauta' => $pauta,
            'creative' => $creative,
            'equipment' => $equipment,
            'aftermovies' => $aftermovies,
            'floats' => $floats,
            'package' => $package,
            'gear' => $gear,
        ];

        $heroProofVideo = LandingPageVideos::toHeroProofVideo($proof);

        return [
            'landingVideos' => $landingVideos,
            'heroProofVideo' => $heroProofVideo,
            'reelLibraryPreview' => $reelLibraryPreview,
            'reelStats' => ReelLibrary::stats(),
        ];
    }

    /**
     * @param  list<array{id: string, title: string, src: string}>  $pool
     * @return list<array{id: string, title: string, src: string}>
     */
    public static function pickEntries(array $pool, int $count): array
    {
        if ($pool === [] || $count <= 0) {
            return [];
        }

        $shuffled = $pool;
        shuffle($shuffled);

        return array_slice($shuffled, 0, min($count, count($shuffled)));
    }

    /**
     * @param  list<?array{src: string, poster: string|null, title: string|null}>  $entries
     * @return list<array{src: string, poster: string|null, title: string|null}>
     */
    protected static function compactEntries(array $entries): array
    {
        return array_values(array_filter($entries, fn (?array $entry): bool => $entry !== null));
    }

    /**
     * @param  list<array{id: string, title: string, src: string}>  $picks
     * @return array{src: string, poster: string|null, title: string|null}|null
     */
    protected static function pickEntryAt(array $picks, int $index): ?array
    {
        if (! isset($picks[$index])) {
            return null;
        }

        return self::toLandingEntry($picks[$index]);
    }

    /**
     * @param  array{id: string, title: string, src: string}  $entry
     * @return array{src: string, poster: string|null, title: string|null}
     */
    protected static function toLandingEntry(array $entry): array
    {
        $src = $entry['src'];
        $poster = self::posterForSrc($src);

        return [
            'src' => $src,
            'poster' => $poster,
            'title' => null,
        ];
    }

    public static function previewCountForUserAgent(?string $userAgent): int
    {
        $desktop = max(1, (int) config('landing.reels_library_preview_count', 10));
        $mobile = max(1, (int) config('landing.reels_library_preview_count_mobile', 6));

        if ($userAgent !== null && self::isMobileUserAgent($userAgent)) {
            return $mobile;
        }

        return $desktop;
    }

    public static function isMobileUserAgent(string $userAgent): bool
    {
        return (bool) preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i', $userAgent);
    }

    protected static function posterForSrc(string $src): ?string
    {
        return ReelLibrary::posterForSrc($src);
    }
}
