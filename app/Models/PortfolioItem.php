<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\Unit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PortfolioItem extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'orientation',
        'source',
        'youtube_url',
        'youtube_id',
        'caption',
        'tags',
        'is_featured',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'tags' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('asset')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/jpg',
                'video/mp4',
                'video/quicktime',
                'video/webm',
            ]);

        $this->addMediaCollection('poster')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/jpg',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (! $media || ! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        $watermarkPath = public_path('images/logo-watermark.png');
        $hasWatermark = file_exists($watermarkPath);

        $thumbConversion = $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 600, 600)
            ->format('jpg')
            ->quality(85)
            ->performOnCollections('asset', 'poster');

        if ($hasWatermark) {
            $thumbConversion->watermark($watermarkPath)
                ->watermarkPosition('bottom-right')
                ->watermarkPadding(20, 20)
                ->watermarkOpacity(60)
                ->watermarkWidth(120, Unit::PIXELS);
        }

        $thumbConversion->nonQueued();

        $largeConversion = $this->addMediaConversion('large')
            ->fit(Fit::Max, 1600, 1200)
            ->format('jpg')
            ->quality(90)
            ->performOnCollections('asset', 'poster');

        if ($hasWatermark) {
            $largeConversion->watermark($watermarkPath)
                ->watermarkPosition('bottom-right')
                ->watermarkPadding(30, 30)
                ->watermarkOpacity(50)
                ->watermarkWidth(150, Unit::PIXELS);
        }

        $largeConversion->nonQueued();

        // Conversión con marca de agua para uso directo (opcional)
        $watermarkedConversion = $this->addMediaConversion('watermarked')
            ->performOnCollections('asset', 'poster');

        if ($hasWatermark) {
            $watermarkedConversion->watermark($watermarkPath)
                ->watermarkPosition('bottom-right')
                ->watermarkPadding(30, 30)
                ->watermarkOpacity(50)
                ->watermarkWidth(150, Unit::PIXELS);
        }

        $watermarkedConversion->nonQueued();
    }

    public function getAssetUrlAttribute(): string
    {
        if ($this->source === 'youtube' && $this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/maxresdefault.jpg";
        }

        $media = $this->getFirstMedia('asset');

        if (! $media) {
            return asset('images/og-default.jpg');
        }

        if ($media->hasGeneratedConversion('large')) {
            return $media->getUrl('large');
        }

        return $media->getUrl();
    }

    public function getPosterUrlAttribute(): string
    {
        $poster = $this->getFirstMedia('poster');

        if ($poster) {
            return $poster->hasGeneratedConversion('large')
                ? $poster->getUrl('large')
                : $poster->getUrl();
        }

        if ($this->source === 'youtube' && $this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/maxresdefault.jpg";
        }

        return $this->asset_url;
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->source !== 'youtube' || ! $this->youtube_id) {
            return null;
        }

        return "https://www.youtube.com/embed/{$this->youtube_id}?rel=0&modestbranding=1&playsinline=1&autoplay=1";
    }
}
