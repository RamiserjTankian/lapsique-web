<?php

namespace App\Http\Resources;

use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PortfolioItem */
class PortfolioItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $assetMedia = $this->getFirstMedia('asset');
        $hasPublicVideo = $this->asset_path && preg_match('/\.(mp4|mov|m4v|webm)$/i', $this->asset_path);
        $isUploadVideo = $assetMedia && str_starts_with((string) $assetMedia->mime_type, 'video/');

        $mediaType = match (true) {
            $this->source === 'youtube' => 'youtube',
            (bool) $hasPublicVideo => 'video',
            $isUploadVideo => 'video',
            default => 'image',
        };

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'source' => $this->source ?? 'upload',
            'caption' => $this->caption,
            'tags' => $this->tags ?? [],
            'asset_url' => $this->asset_url,
            'poster_url' => $this->poster_url ?? $this->asset_url,
            'playback_url' => $hasPublicVideo ? $this->asset_url : ($isUploadVideo ? $assetMedia->getUrl() : null),
            'embed_url' => $this->embed_url,
            'youtube_id' => $this->youtube_id,
            'youtube_url' => $this->youtube_url,
            'media_type' => $mediaType,
            'is_featured' => (bool) $this->is_featured,
            'orientation' => $this->orientation,
        ];
    }
}
