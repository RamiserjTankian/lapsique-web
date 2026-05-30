<?php

namespace App\Http\Resources;

use App\Models\Dj;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Dj */
class DjResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profileThumb = $this->readableFirstMediaUrl('profile', 'thumb');
        $galleryThumb = $this->readableFirstMediaUrl('gallery', 'thumb');

        $gallery = $this->getMedia('gallery')->map(fn ($media) => [
            'id' => $media->id,
            'url' => $this->readableMediaUrl($media, 'large') ?? $this->readableMediaUrl($media),
            'thumb_url' => $this->readableMediaUrl($media, 'thumb') ?? $this->readableMediaUrl($media),
        ])->values()->all();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'avatar_url' => $profileThumb ?: $galleryThumb ?: null,
            'cover_url' => $this->readableFirstMediaUrl('profile', 'hero')
                ?: $this->readableFirstMediaUrl('profile', 'card')
                ?: $profileThumb
                ?: null,
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

            return is_readable($path) ? $media->getUrl($conversion) : null;
        }

        $path = $media->getPath();

        return is_readable($path) ? $media->getUrl() : null;
    }
}
