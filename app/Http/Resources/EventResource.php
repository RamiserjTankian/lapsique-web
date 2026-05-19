<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'cover_url' => $this->getFirstMediaUrl('cover', 'large') ?: null,
            'location_name' => $this->location?->name,
            'description' => $this->description,
        ];
    }
}
