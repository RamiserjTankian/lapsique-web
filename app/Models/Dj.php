<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Dj extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected static ?array $eventPivotColumns = null;

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'instagram_handle',
        'instagram_url',
        'youtube_url',
        'soundcloud_url',
        'website_url',
        'public_image_path',
        'is_featured',
        'trascendental_roster',
        'booking_status',
        'nationality',
        'record_label',
        'is_highlighted',
        'priority',
        'tags',
        'technical_rider',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'trascendental_roster' => 'boolean',
        'is_highlighted' => 'boolean',
        'priority' => 'integer',
        'tags' => 'array',
        'technical_rider' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Square thumb for avatars / admin
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 500, 500)
            ->format('jpg')
            ->quality(85)
            ->performOnCollections('profile', 'gallery')
            ->nonQueued();

        // Card crop (16:9) to match lineup cards
        $this->addMediaConversion('card')
            ->fit(Fit::Crop, 1200, 675)
            ->format('jpg')
            ->quality(88)
            ->performOnCollections('profile')
            ->nonQueued();

        // Hero cover (wide) for detail page
        $this->addMediaConversion('hero')
            ->fit(Fit::Crop, 1600, 900)
            ->format('jpg')
            ->quality(90)
            ->performOnCollections('profile')
            ->nonQueued();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)
            ->withPivot($this->availableEventPivotColumns());
    }

    public function rps(): BelongsToMany
    {
        return $this->belongsToMany(Rp::class, 'rp_dj')
            ->withTimestamps();
    }

    public function guestListEntries(): HasMany
    {
        return $this->hasMany(GuestListEntry::class);
    }

    public function getGuestListCountForEvent(int $eventId): int
    {
        return $this->guestListEntries()
            ->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'confirmed', 'attended'])
            ->count();
    }

    public function getGuestLimitForEvent(int $eventId): ?int
    {
        $pivot = $this->events()
            ->where('events.id', $eventId)
            ->first()?->pivot;

        return $pivot?->guest_limit;
    }

    public function canAddMoreGuests(int $eventId): bool
    {
        $limit = $this->getGuestLimitForEvent($eventId);
        if ($limit === null) {
            return true; // Sin límite
        }

        return $this->getGuestListCountForEvent($eventId) < $limit;
    }

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class)
            ->withPivot('position')
            ->orderByPivot('position')
            ->orderByDesc('published_at');
    }

    protected function availableEventPivotColumns(): array
    {
        $available = self::$eventPivotColumns ??= Schema::getColumnListing('dj_event');

        return array_values(array_intersect(
            ['role', 'position', 'time_slot', 'guest_limit', 'b2b_with_dj_id'],
            $available
        ));
    }
}
