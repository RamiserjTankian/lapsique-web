<?php

namespace App\Http\Resources;

use App\Models\PortfolioItem;
use App\Support\BrowserUrl;
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
        $assetUrl = BrowserUrl::normalize($this->resourceAssetUrl($assetMedia)) ?? '';
        $posterUrl = BrowserUrl::normalize($this->resourcePosterUrl($assetUrl)) ?? '';

        $mediaType = match (true) {
            $this->source === 'youtube' => 'youtube',
            (bool) $hasPublicVideo => 'video',
            $isUploadVideo => 'video',
            default => 'image',
        };

        return [
            'id' => $this->id,
            'title' => null,
            'slug' => $this->slug,
            'type' => $this->type,
            'source' => $this->source ?? 'upload',
            'caption' => null,
            'tags' => [],
            'asset_url' => $assetUrl,
            'poster_url' => $posterUrl,
            'playback_url' => $hasPublicVideo ? $assetUrl : ($isUploadVideo ? BrowserUrl::normalize($assetMedia->getUrl()) : null),
            'embed_url' => $this->embed_url,
            'youtube_id' => $this->youtube_id,
            'youtube_url' => $this->youtube_url,
            'media_type' => $mediaType,
            'is_featured' => (bool) $this->is_featured,
            'orientation' => $this->orientation,
        ];
    }

    private function resourceAssetUrl($assetMedia): string
    {
        if ($this->asset_path) {
            return asset(ltrim($this->asset_path, '/'));
        }

        if ($this->source === 'youtube' && $this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/maxresdefault.jpg";
        }

        return $assetMedia?->getUrl() ?? asset('images/og-default.jpg');
    }

    private function resourcePosterUrl(string $assetUrl): string
    {
        if ($this->poster_path) {
            return asset(ltrim($this->poster_path, '/'));
        }

        $posterMedia = $this->getFirstMedia('poster');

        if ($posterMedia) {
            return $posterMedia->getUrl();
        }

        if ($this->source === 'youtube' && $this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/maxresdefault.jpg";
        }

        return $assetUrl;
    }
}
