<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Video extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'youtube_id',
        'youtube_url',
        'thumbnail_url',
        'location',
        'maps_url',
        'description',
        'published_at',
        'is_featured',
        'priority',
        'tags',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'priority' => 'integer',
        'tags' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function djs(): BelongsToMany
    {
        return $this->belongsToMany(Dj::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')
            ->singleFile()
            ->useFallbackUrl($this->thumbnail_url ?? '')
            ->useFallbackPath($this->thumbnail_url ?? '');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(640)
            ->height(360)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(1280)
            ->height(720)
            ->sharpen(10)
            ->nonQueued();
    }

    public function getThumbnailUrlAttribute($value): string
    {
        // Prioridad: 1. Imagen subida, 2. URL de YouTube, 3. Default
        $uploadedThumbnail = $this->getFirstMediaUrl('thumbnail', 'large');
        
        if ($uploadedThumbnail) {
            return $uploadedThumbnail;
        }
        
        if ($value) {
            return $value;
        }
        
        if ($this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/maxresdefault.jpg";
        }
        
        return asset('images/video-placeholder.jpg');
    }
}
