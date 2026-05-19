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
        $profileThumb = $this->getFirstMediaUrl('profile', 'thumb');
        $galleryThumb = $this->getFirstMediaUrl('gallery', 'thumb');

        $gallery = $this->getMedia('gallery')->map(fn ($media) => [
            'id' => $media->id,
            'url' => $media->hasGeneratedConversion('large')
                ? $media->getUrl('large')
                : $media->getUrl(),
            'thumb_url' => $media->hasGeneratedConversion('thumb')
                ? $media->getUrl('thumb')
                : $media->getUrl(),
        ])->values()->all();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'avatar_url' => $profileThumb ?: $galleryThumb ?: null,
            'cover_url' => $this->getFirstMediaUrl('profile', 'hero')
                ?: $this->getFirstMediaUrl('profile', 'card')
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
}
