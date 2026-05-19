<?php

namespace App\Http\Resources;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Video */
class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'thumbnail_url' => $this->thumbnail_url,
            'youtube_url' => $this->youtube_url,
            'youtube_id' => $this->youtube_id,
            'tags' => $this->tags ?? [],
            'is_featured' => (bool) $this->is_featured,
            'description' => $this->description,
            'location' => $this->location,
            'published_at' => $this->published_at?->toIso8601String(),
            'djs' => $this->whenLoaded(
                'djs',
                fn () => VideoDjSummaryResource::collection($this->djs)->resolve(),
                [],
            ),
        ];
    }
}
