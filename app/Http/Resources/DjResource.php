<?php

namespace App\Http\Resources;

use App\Models\Dj;
use App\Support\BrowserUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Dj */
class DjResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profileThumb = $this->readableFirstMediaUrl('profile', 'thumb');
        $galleryThumb = $this->readableFirstMediaUrl('gallery', 'thumb');
        $fallbackImage = $this->fallbackPublicImageUrl();

        $gallery = $this->getMedia('gallery')->map(fn ($media) => [
            'id' => $media->id,
            'url' => $this->readableMediaUrl($media, 'large') ?? $this->readableMediaUrl($media),
            'thumb_url' => $this->readableMediaUrl($media, 'thumb') ?? $this->readableMediaUrl($media),
        ])->values()->all();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'avatar_url' => $profileThumb ?: $galleryThumb ?: $fallbackImage,
            'cover_url' => $this->readableFirstMediaUrl('profile', 'hero')
                ?: $this->readableFirstMediaUrl('profile', 'card')
                ?: $profileThumb
                ?: $fallbackImage,
            'bio' => $this->bio,
            'instagram_handle' => $this->instagram_handle,
            'youtube_url' => $this->youtube_url,
            'soundcloud_url' => $this->soundcloud_url,
            'website_url' => $this->website_url,
            'technical_rider' => $this->technical_rider ?? [],
            'gallery' => $gallery,
            'is_featured' => (bool) $this->is_featured,
            'is_highlighted' => (bool) $this->is_highlighted,
            'tags' => $this->tags ?? [],
        ];
    }

    private function readableFirstMediaUrl(string $collection, ?string $conversion = null): ?string
    {
        $media = $this->getFirstMedia($collection);

        return $media ? $this->readableMediaUrl($media, $conversion) : null;
    }

    private function readableMediaUrl($media, ?string $conversion = null): ?string
    {
        if ($conversion !== null) {
            if (! $media->hasGeneratedConversion($conversion)) {
                return null;
            }

            $path = $media->getPath($conversion);

            return is_readable($path) ? $this->browserReadableUrl($media->getUrl($conversion)) : null;
        }

        $path = $media->getPath();

        return is_readable($path) ? $this->browserReadableUrl($media->getUrl()) : null;
    }

    private function fallbackPublicImageUrl(): ?string
    {
        $path = $this->public_image_path ?: $this->fallbackPublicImagePath();

        if (! $path) {
            return null;
        }

        $path = ltrim($path, '/');

        return is_readable(public_path($path)) ? $this->browserReadableUrl(asset($path)) : null;
    }

    private function fallbackPublicImagePath(): ?string
    {
        return match ($this->slug) {
            'bryz' => 'images/djs/bryz.jpg',
            'cedrick', 'cc-tdl', 'c-c-tdl', 'cctdl' => 'images/djs/cedrick.jpg',
            'jimbo', 'jimbo-star' => 'images/djs/jimbo-star.jpg',
            'john-pavas' => 'images/djs/john-pavas.jpg',
            'kalani' => 'images/djs/kalani.jpg',
            'kapi' => 'images/djs/kapi.jpg',
            'lagunes-jr' => 'images/djs/lagunes-jr.jpg',
            'rui', 'ru-i' => 'images/djs/rui.jpg',
            'baruc', 'baruck' => 'images/portfolio/video-posters/079-mac-proyectos-baruc-reel.jpg',
            default => null,
        };
    }

    private function browserReadableUrl(string $url): string
    {
        return BrowserUrl::normalize($url) ?? $url;
    }
}
