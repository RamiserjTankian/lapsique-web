<?php

namespace App\Http\Resources;

use App\Models\Dj;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin Dj */
class VideoDjSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profileThumb = $this->readableFirstMediaUrl('profile', 'thumb');
        $galleryThumb = $this->readableFirstMediaUrl('gallery', 'thumb');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'avatar_url' => $profileThumb ?: $galleryThumb ?: null,
            'bio' => $this->bio ? Str::limit(strip_tags($this->bio), 160) : null,
        ];
    }

    private function readableFirstMediaUrl(string $collection, ?string $conversion = null): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (! $media) {
            return null;
        }

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
