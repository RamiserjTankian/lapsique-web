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
        $profileThumb = $this->getFirstMediaUrl('profile', 'thumb');
        $galleryThumb = $this->getFirstMediaUrl('gallery', 'thumb');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'avatar_url' => $profileThumb ?: $galleryThumb ?: null,
            'bio' => $this->bio ? Str::limit(strip_tags($this->bio), 160) : null,
        ];
    }
}
