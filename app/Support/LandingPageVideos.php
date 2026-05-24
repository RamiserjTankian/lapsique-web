<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class LandingPageVideos
{
    public static function manifestPath(): string
    {
        return public_path((string) config('landing.output_dir').'/manifest.json');
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function manifest(): ?array
    {
        $path = self::manifestPath();

        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    public static function isAvailable(): bool
    {
        $manifest = self::manifest();

        return $manifest !== null && filled($manifest['hero']['src'] ?? null);
    }

    /**
     * @return array{
     *     hero: array{src: string, poster: string|null, title: string|null}|null,
     *     offer: array{src: string, poster: string|null, title: string|null}|null,
     *     proof: array{src: string, poster: string|null, title: string|null}|null,
     *     pauta: array{src: string, poster: string|null, title: string|null}|null,
     *     creative: list<array{src: string, poster: string|null, title: string|null}>,
     *     equipment: list<array{src: string, poster: string|null, title: string|null}>,
     *     aftermovies: list<array{src: string, poster: string|null, title: string|null}>,
     *     floats: list<array{src: string, poster: string|null, title: string|null}>,
     *     package: array{src: string, poster: string|null, title: string|null}|null,
     *     gear: array{src: string, poster: string|null, title: string|null}|null,
     * }|null
     */
    public static function forHome(): ?array
    {
        $manifest = self::manifest();

        if ($manifest === null) {
            return null;
        }

        $hero = self::normalizeEntry($manifest['hero'] ?? null);
        $offer = self::normalizeEntry($manifest['offer'] ?? null);
        $proof = self::normalizeEntry($manifest['proof'] ?? null);
        $pauta = self::normalizeEntry($manifest['pauta'] ?? null);
        $package = self::normalizeEntry($manifest['package'] ?? null);
        $gear = self::normalizeEntry($manifest['gear'] ?? null);

        $creative = self::normalizeList($manifest['creative'] ?? []);
        $equipment = self::normalizeList($manifest['equipment'] ?? []);
        $aftermovies = self::normalizeList($manifest['aftermovies'] ?? []);
        $floats = self::normalizeList($manifest['floats'] ?? []);

        if ($hero === null && $offer === null && $creative === []) {
            return null;
        }

        while (count($creative) < 3 && $hero !== null) {
            $creative[] = $hero;
        }

        while (count($equipment) < 2 && isset($creative[0])) {
            $equipment[] = $creative[count($equipment) % count($creative)];
        }

        while (count($aftermovies) < 4 && $hero !== null) {
            $aftermovies[] = $hero;
        }

        while (count($floats) < 2 && $proof !== null) {
            $floats[] = $proof;
        }

        return [
            'hero' => $hero,
            'offer' => $offer ?? $hero,
            'proof' => $proof ?? $hero,
            'pauta' => $pauta ?? ($creative[0] ?? $hero),
            'creative' => array_slice($creative, 0, 3),
            'equipment' => array_slice($equipment, 0, 2),
            'aftermovies' => array_slice($aftermovies, 0, 4),
            'floats' => array_slice($floats, 0, 2),
            'package' => $package ?? $offer,
            'gear' => $gear ?? $hero,
        ];
    }

    /**
     * @param  array{src: string, poster: string|null, title: string|null}|null  $entry
     * @return array{
     *     title: string|null,
     *     media_type: 'video',
     *     embed_url: null,
     *     playback_url: string,
     *     poster_url: string|null,
     * }|null
     */
    public static function toHeroProofVideo(?array $entry): ?array
    {
        if ($entry === null || ! self::publicFileExists($entry['src'])) {
            return null;
        }

        return [
            'title' => $entry['title'],
            'media_type' => 'video',
            'embed_url' => null,
            'playback_url' => $entry['src'],
            'poster_url' => $entry['poster'],
        ];
    }

    /**
     * @param  list<mixed>  $items
     * @return list<array{src: string, poster: string|null, title: string|null}>
     */
    protected static function normalizeList(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $entry = self::normalizeEntry($item);

            if ($entry !== null) {
                $normalized[] = $entry;
            }
        }

        return $normalized;
    }

    /**
     * @return array{src: string, poster: string|null, title: string|null}|null
     */
    protected static function normalizeEntry(mixed $raw): ?array
    {
        if (! is_array($raw) || blank($raw['src'] ?? null)) {
            return null;
        }

        $src = (string) $raw['src'];

        if (! self::publicFileExists($src)) {
            return null;
        }

        $poster = filled($raw['poster'] ?? null) ? (string) $raw['poster'] : null;

        if ($poster !== null && ! self::publicFileExists($poster)) {
            $poster = null;
        }

        return [
            'src' => $src,
            'poster' => $poster,
            'title' => filled($raw['title'] ?? null) ? (string) $raw['title'] : null,
        ];
    }

    public static function publicFileExists(string $publicPath): bool
    {
        if (! str_starts_with($publicPath, '/')) {
            return false;
        }

        $fullPath = public_path(ltrim($publicPath, '/'));

        return is_file($fullPath);
    }
}
