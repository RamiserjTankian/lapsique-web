<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'headline',
        'description',
        'starts_at',
        'venue',
        'city',
        'youtube_url',
        'ticket_url',
        'is_featured',
        'priority',
        'featured_poster',
        'has_vertical_poster',
        'has_horizontal_poster',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'is_featured' => 'boolean',
        'priority' => 'integer',
        'has_vertical_poster' => 'boolean',
        'has_horizontal_poster' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile();

        $this->addMediaCollection('cover_vertical')
            ->singleFile();

        $this->addMediaCollection('cover_horizontal')
            ->singleFile();

        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 600, 600)
            ->nonQueued();

        $this->addMediaConversion('cover_large')
            ->fit(Manipulations::FIT_MAX, 1600, 900)
            ->nonQueued();

        $this->addMediaConversion('poster_vertical')
            ->fit(Manipulations::FIT_MAX, 1080, 1920)
            ->nonQueued();

        $this->addMediaConversion('poster_horizontal')
            ->fit(Manipulations::FIT_MAX, 1920, 1080)
            ->nonQueued();
    }

    public function guests(): HasMany
    {
        return $this->hasMany(GuestListEntry::class);
    }

    public function djs(): BelongsToMany
    {
        return $this->belongsToMany(Dj::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
