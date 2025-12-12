<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dj extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'instagram_handle',
        'youtube_url',
        'soundcloud_url',
        'website_url',
        'is_featured',
        'priority',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'priority' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile();

        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 500, 500)
            ->nonQueued();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class);
    }
}
