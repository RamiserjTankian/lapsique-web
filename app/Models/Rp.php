<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rp extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'whatsapp',
        'instagram_handle',
        'notes',
        'status',
        'commission_rate',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'status' => 'string',
    ];

    // Relaciones
    public function djs(): BelongsToMany
    {
        return $this->belongsToMany(Dj::class, 'rp_dj')
            ->withTimestamps();
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'rp_customer')
            ->withTimestamps();
    }

    public function guestListEntries(): HasMany
    {
        return $this->hasMany(GuestListEntry::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'rp_event')
            ->withPivot(['commission_rate', 'notes'])
            ->withTimestamps();
    }

    public function ticketOrders(): HasMany
    {
        return $this->hasMany(TicketOrder::class);
    }

    public function ticketAttendees(): HasMany
    {
        return $this->hasMany(TicketAttendee::class);
    }

    // Métodos de utilidad
    public function getTotalGuestListCountAttribute(): int
    {
        return $this->guestListEntries()->count();
    }

    public function getConfirmedGuestListCountAttribute(): int
    {
        return $this->guestListEntries()->where('status', 'confirmed')->count();
    }

    public function getAttendedGuestListCountAttribute(): int
    {
        return $this->guestListEntries()->where('status', 'attended')->count();
    }

    /**
     * Obtener estadísticas de guest list para un evento específico
     */
    public function getGuestListStatsForEvent(int $eventId): array
    {
        $entries = $this->guestListEntries()->where('event_id', $eventId)->get();
        
        return [
            'total' => $entries->count(),
            'pending' => $entries->where('status', 'pending')->count(),
            'confirmed' => $entries->where('status', 'confirmed')->count(),
            'attended' => $entries->where('status', 'attended')->count(),
            'cancelled' => $entries->where('status', 'cancelled')->count(),
            'no_show' => $entries->where('status', 'no_show')->count(),
        ];
    }

    /**
     * Obtener contador de guest list para un evento específico
     */
    public function getGuestListCountForEvent(int $eventId): int
    {
        return $this->guestListEntries()
            ->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'confirmed', 'attended'])
            ->count();
    }
}
