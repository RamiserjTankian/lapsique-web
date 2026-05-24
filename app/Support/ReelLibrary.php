<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class ReelLibrary
{
    public static function reelsDir(): string
    {
        return public_path((string) config('landing.reels_dir', 'videos/reels'));
    }

    public static function manifestPath(): string
    {
        return public_path((string) config('landing.reels_manifest', 'videos/reels/manifest.json'));
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

    /**
     * @return list<array{id: string, title: string, src: string}>
     */
    public static function all(): array
    {
        $manifest = self::manifest();

        if ($manifest !== null && is_array($manifest['entries'] ?? null)) {
            return self::normalizeEntries($manifest['entries']);
        }

        return self::scanDirectory();
    }

    public static function count(): int
    {
        return count(self::all());
    }

    /**
     * @return array{totalSourceVideos: int, uniqueVideos: int}
     */
    public static function stats(): array
    {
        $manifest = self::manifest();
        $uniqueVideos = self::count();

        $totalSourceVideos = (int) ($manifest['total_source_videos'] ?? config('landing.reels_total_source_videos', 101));

        if ($totalSourceVideos < $uniqueVideos) {
            $totalSourceVideos = $uniqueVideos;
        }

        return [
            'totalSourceVideos' => $totalSourceVideos,
            'uniqueVideos' => $uniqueVideos,
        ];
    }

    public static function titleFromFilename(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $base = preg_replace('/^\d+-/', '', $base) ?? $base;
        $base = str_replace('-', ' ', $base);

        return ucwords($base);
    }

    public static function isExcludedReel(string $value): bool
    {
        return (bool) preg_match('/soundwaves|\-sw\-|provenza[\s_-]?marcha|rebolledo[\s_-]?fix|ad[\s_-]?sudbeat|demerry[\s_-]reel[\s_-]1vertical|early[\s_-]?render|reel[\s_-]bryz[\s_-]1|reel[\s_-]empleados|046-bluepointrs-reel/i', $value);
    }

    /**
     * @param  list<mixed>  $entries
     * @return list<array{id: string, title: string, src: string}>
     */
    protected static function normalizeEntries(array $entries): array
    {
        $normalized = [];

        foreach ($entries as $entry) {
            if (! is_array($entry) || blank($entry['src'] ?? null)) {
                continue;
            }

            $src = (string) $entry['src'];

            if (! self::publicFileExists($src) || self::isExcludedReel($src)) {
                continue;
            }

            $title = filled($entry['title'] ?? null)
                ? (string) $entry['title']
                : self::titleFromFilename(basename($src));

            if (self::isExcludedReel($title)) {
                continue;
            }

            $normalized[] = [
                'id' => filled($entry['id'] ?? null) ? (string) $entry['id'] : 'reel-'.(count($normalized) + 1),
                'title' => $title,
                'src' => $src,
            ];
        }

        return $normalized;
    }

    /**
     * @return list<array{id: string, title: string, src: string}>
     */
    protected static function scanDirectory(): array
    {
        $dir = self::reelsDir();

        if (! is_dir($dir)) {
            return [];
        }

        $files = collect(File::files($dir))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'mp4')
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        $entries = [];

        foreach ($files as $index => $file) {
            $filename = $file->getFilename();

            if (self::isExcludedReel($filename)) {
                continue;
            }

            $src = '/'.trim((string) config('landing.reels_dir', 'videos/reels'), '/').'/'.$filename;

            if (! self::publicFileExists($src)) {
                continue;
            }

            $entries[] = [
                'id' => sprintf('reel-%03d', $index + 1),
                'title' => self::titleFromFilename($filename),
                'src' => $src,
            ];
        }

        return $entries;
    }

    protected static function publicFileExists(string $publicPath): bool
    {
        if (! str_starts_with($publicPath, '/')) {
            return false;
        }

        return is_file(public_path(ltrim($publicPath, '/')));
    }
}
