<?php

namespace App\Support;

use App\Http\Resources\PortfolioItemResource;
use App\Models\PortfolioItem;
use App\Models\SiteSetting;
use App\Models\Video;
use Illuminate\Support\Collection;

class HomeHeroProofVideos
{
    /**
     * @param  Collection<int, PortfolioItem>  $portfolioItems
     * @return list<array{
     *     title: string|null,
     *     media_type: 'youtube'|'video'|'image',
     *     embed_url: string|null,
     *     playback_url: string|null,
     *     poster_url: string|null,
     * }>
     */
    public static function resolve(?SiteSetting $settings, Collection $portfolioItems): array
    {
        if ($settings) {
            $slot = $settings->homeHeroProofSlot();

            $resolved = self::resolveSlot($slot['title'], $slot['source'], $slot['reference']);

            if ($resolved !== null) {
                return [$resolved];
            }
        }

        $fallbackVideo = $portfolioItems
            ->first(fn (PortfolioItem $item) => self::portfolioItemIsPlayable($item));

        if ($fallbackVideo) {
            return [self::fromPortfolioItem($fallbackVideo)];
        }

        $fallbackImage = $portfolioItems
            ->first(fn (PortfolioItem $item) => filled($item->poster_url) || filled($item->asset_url));

        if ($fallbackImage) {
            return [self::fromPortfolioItemImage($fallbackImage)];
        }

        return [];
    }

    /**
     * @return array{
     *     title: string|null,
     *     media_type: 'youtube'|'video'|'image',
     *     embed_url: string|null,
     *     playback_url: string|null,
     *     poster_url: string|null,
     * }|null
     */
    public static function resolveSlot(?string $title, ?string $source, ?string $reference): ?array
    {
        if (blank($source) || blank($reference)) {
            return null;
        }

        return match ($source) {
            'portfolio_item' => self::fromPortfolioItemId((int) $reference, $title),
            'video' => self::fromVideoId((int) $reference, $title),
            'youtube' => self::fromYoutubeReference($reference, $title),
            'url' => self::fromDirectUrl($reference, $title),
            default => null,
        };
    }

    public static function youtubeMutedLoopEmbedUrl(string $youtubeId): string
    {
        $query = http_build_query([
            'rel' => 0,
            'modestbranding' => 1,
            'playsinline' => 1,
            'mute' => 1,
            'autoplay' => 1,
            'loop' => 1,
            'playlist' => $youtubeId,
            'controls' => 0,
        ]);

        return "https://www.youtube.com/embed/{$youtubeId}?{$query}";
    }

    public static function extractYoutubeId(string $value): ?string
    {
        $value = trim($value);

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $value)) {
            return $value;
        }

        if (preg_match('/(?:v=|\/)([\w-]{11})/', $value, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @return array{
     *     title: string|null,
     *     media_type: 'youtube'|'video'|'image',
     *     embed_url: string|null,
     *     playback_url: string|null,
     *     poster_url: string|null,
     * }|null
     */
    protected static function fromPortfolioItemId(int $id, ?string $title): ?array
    {
        $item = PortfolioItem::query()
            ->where('is_active', true)
            ->with('media')
            ->find($id);

        if (! $item || ! self::portfolioItemIsPlayable($item)) {
            return null;
        }

        $resolved = self::fromPortfolioItem($item);

        if (filled($title)) {
            $resolved['title'] = $title;
        }

        return $resolved;
    }

    /**
     * @return array{
     *     title: string|null,
     *     media_type: 'youtube'|'video'|'image',
     *     embed_url: string|null,
     *     playback_url: string|null,
     *     poster_url: string|null,
     * }|null
     */
    protected static function fromVideoId(int $id, ?string $title): ?array
    {
        $video = Video::query()->find($id);

        if (! $video) {
            return null;
        }

        $youtubeId = $video->youtube_id ?: self::extractYoutubeId((string) $video->youtube_url);

        if (! $youtubeId) {
            return null;
        }

        return [
            'title' => $title ?: $video->title,
            'media_type' => 'youtube',
            'embed_url' => self::youtubeMutedLoopEmbedUrl($youtubeId),
            'playback_url' => null,
            'poster_url' => $video->thumbnail_url,
        ];
    }

    /**
     * @return array{
     *     title: string|null,
     *     media_type: 'youtube'|'video'|'image',
     *     embed_url: string|null,
     *     playback_url: string|null,
     *     poster_url: string|null,
     * }|null
     */
    protected static function fromYoutubeReference(string $reference, ?string $title): ?array
    {
        $youtubeId = self::extractYoutubeId($reference);

        if (! $youtubeId) {
            return null;
        }

        return [
            'title' => $title,
            'media_type' => 'youtube',
            'embed_url' => self::youtubeMutedLoopEmbedUrl($youtubeId),
            'playback_url' => null,
            'poster_url' => "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg",
        ];
    }

    /**
     * @return array{
     *     title: string|null,
     *     media_type: 'youtube'|'video'|'image',
     *     embed_url: string|null,
     *     playback_url: string|null,
     *     poster_url: string|null,
     * }|null
     */
    protected static function fromDirectUrl(string $url, ?string $title): ?array
    {
        $url = trim($url);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $youtubeId = self::extractYoutubeId($url);

        if ($youtubeId) {
            return self::fromYoutubeReference($youtubeId, $title);
        }

        return [
            'title' => $title,
            'media_type' => 'video',
            'embed_url' => null,
            'playback_url' => $url,
            'poster_url' => null,
        ];
    }

    /**
     * @return array{
     *     title: string|null,
     *     media_type: 'youtube'|'video'|'image',
     *     embed_url: string|null,
     *     playback_url: string|null,
     *     poster_url: string|null,
     * }
     */
    protected static function fromPortfolioItem(PortfolioItem $item): array
    {
        /** @var array<string, mixed> $data */
        $data = (new PortfolioItemResource($item))->resolve();

        if ($data['media_type'] === 'youtube' && filled($data['youtube_id'] ?? null)) {
            return [
                'title' => $data['title'],
                'media_type' => 'youtube',
                'embed_url' => self::youtubeMutedLoopEmbedUrl((string) $data['youtube_id']),
                'playback_url' => null,
                'poster_url' => $data['poster_url'],
            ];
        }

        if ($data['media_type'] === 'video' && filled($data['playback_url'] ?? null)) {
            return [
                'title' => $data['title'],
                'media_type' => 'video',
                'embed_url' => null,
                'playback_url' => $data['playback_url'],
                'poster_url' => $data['poster_url'],
            ];
        }

        return self::fromPortfolioItemImage($item);
    }

    /**
     * @return array{
     *     title: string|null,
     *     media_type: 'youtube'|'video'|'image',
     *     embed_url: string|null,
     *     playback_url: string|null,
     *     poster_url: string|null,
     * }
     */
    protected static function fromPortfolioItemImage(PortfolioItem $item): array
    {
        return [
            'title' => $item->title,
            'media_type' => 'image',
            'embed_url' => null,
            'playback_url' => null,
            'poster_url' => $item->poster_url ?? $item->asset_url,
        ];
    }

    protected static function portfolioItemIsPlayable(PortfolioItem $item): bool
    {
        if ($item->source === 'youtube' && filled($item->youtube_id)) {
            return true;
        }

        $assetMedia = $item->getFirstMedia('asset');

        return $assetMedia && str_starts_with((string) $assetMedia->mime_type, 'video/');
    }
}
