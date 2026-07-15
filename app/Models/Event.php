<?php

namespace App\Models;

use App\Support\EventLineup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected static ?array $djEventPivotColumns = null;

    protected $fillable = [
        'title',
        'slug',
        'headline',
        'description',
        'lineup_text',
        'starts_at',
        'venue',
        'city',
        'location_id',
        'technical_rider',
        'tags',
        'youtube_url',
        'ticket_url',
        'public_image_path',
        'source_url',
        'details_url',
        'is_featured',
        'is_case_study',
        'trascendental_kind',
        'trascendental_visible',
        'case_summary',
        'case_metrics',
        'case_services',
        'case_sort',
        'priority',
        'featured_poster',
        'has_vertical_poster',
        'has_horizontal_poster',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_case_study' => 'boolean',
        'trascendental_visible' => 'boolean',
        'case_metrics' => 'array',
        'case_services' => 'array',
        'case_sort' => 'integer',
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

    public function registerMediaConversions(?Media $media = null): void
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
            ->withPivot($this->availableDjEventPivotColumns())
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

    public function ticketProducts(): HasMany
    {
        return $this->hasMany(TicketProduct::class);
    }

    public function ticketOrders(): HasMany
    {
        return $this->hasMany(TicketOrder::class);
    }

    public function ticketAttendees(): HasMany
    {
        return $this->hasMany(TicketAttendee::class);
    }

    public function customerBalances(): HasMany
    {
        return $this->hasMany(CustomerEventBalance::class);
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

    public function getLineupEntriesByRole(string $role): array
    {
        return EventLineup::displayEntries(
            $this->djs->where('pivot.role', $role)->values()
        )->all();
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

    public function getSalesBalanceAttribute(): array
    {
        return [
            'paid_orders' => (int) ($this->paid_ticket_orders_count ?? 0),
            'gross_revenue' => (float) ($this->paid_ticket_revenue ?? 0),
            'net_revenue' => (float) ($this->paid_ticket_subtotal ?? 0),
            'service_fees' => (float) ($this->paid_ticket_fee ?? 0),
            'tickets_sold' => (int) ($this->paid_ticket_accesses ?? 0),
            'tickets_registered' => (int) ($this->registered_ticket_accesses ?? 0),
        ];
    }

    protected function availableDjEventPivotColumns(): array
    {
        $available = self::$djEventPivotColumns ??= Schema::getColumnListing('dj_event');

        return array_values(array_intersect(
            ['role', 'position', 'time_slot', 'guest_limit', 'b2b_with_dj_id'],
            $available
        ));
    }
}
