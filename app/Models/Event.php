<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
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
        'location_id',
        'technical_rider',
        'tags',
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
        'technical_rider' => 'array',
        'tags' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);

        $this->addMediaCollection('cover_vertical')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);

        $this->addMediaCollection('cover_horizontal')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);

        $this->addMediaCollection('venue_gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 600, 600)
            ->format('jpg')
            ->quality(85)
            ->nonQueued();

        $this->addMediaConversion('cover_large')
            ->fit(Fit::Max, 1600, 900)
            ->format('jpg')
            ->quality(90)
            ->nonQueued();

        $this->addMediaConversion('poster_vertical')
            ->fit(Fit::Max, 1080, 1920)
            ->format('jpg')
            ->quality(90)
            ->nonQueued();

        $this->addMediaConversion('poster_horizontal')
            ->fit(Fit::Max, 1920, 1080)
            ->format('jpg')
            ->quality(90)
            ->nonQueued();
    }

    public function guests(): HasMany
    {
        return $this->hasMany(GuestListEntry::class);
    }

    public function djs(): BelongsToMany
    {
        return $this->belongsToMany(Dj::class)
            ->withPivot(['role', 'position', 'time_slot'])
            ->orderByRaw("FIELD(dj_event.role, 'headliner', 'warmup') asc")
            ->orderBy('dj_event.position')
            ->orderBy('djs.name');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function rps(): BelongsToMany
    {
        return $this->belongsToMany(Rp::class, 'rp_event')
            ->withPivot(['commission_rate', 'notes'])
            ->withTimestamps();
    }

    public function getGuestListCountForDj(int $djId): int
    {
        return $this->guests()
            ->where('dj_id', $djId)
            ->whereIn('status', ['pending', 'confirmed', 'attended'])
            ->count();
    }

    public function getGuestLimitForDj(int $djId): ?int
    {
        $pivot = $this->djs()
            ->where('djs.id', $djId)
            ->first()?->pivot;

        return $pivot?->guest_limit;
    }

    /**
     * Obtener estadísticas de guest list por RP para este evento
     */
    public function getGuestListStatsByRp(): array
    {
        $stats = [];
        
        foreach ($this->rps as $rp) {
            $stats[$rp->id] = [
                'rp' => $rp,
                'stats' => $rp->getGuestListStatsForEvent($this->id),
            ];
        }
        
        return $stats;
    }

    /**
     * Obtener contador de guest list para un RP específico
     */
    public function getGuestListCountForRp(int $rpId): int
    {
        return $this->guests()
            ->where('rp_id', $rpId)
            ->whereIn('status', ['pending', 'confirmed', 'attended'])
            ->count();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
