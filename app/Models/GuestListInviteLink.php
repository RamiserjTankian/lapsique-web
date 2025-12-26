<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GuestListInviteLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'dj_id',
        'rp_id',
        'token',
        'name',
        'is_active',
        'max_registrations',
        'current_registrations',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_registrations' => 'integer',
        'current_registrations' => 'integer',
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'invite_url',
    ];

    // Relaciones
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function dj(): BelongsTo
    {
        return $this->belongsTo(Dj::class);
    }

    public function rp(): BelongsTo
    {
        return $this->belongsTo(Rp::class);
    }

    public function guestListEntries(): HasMany
    {
        return $this->hasMany(GuestListEntry::class, 'invite_link_id');
    }

    // Métodos de utilidad
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function getInviteUrlAttribute(): string
    {
        return route('guestlist.register', ['token' => $this->token]);
    }

    public function canAcceptMoreRegistrations(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_registrations && $this->current_registrations >= $this->max_registrations) {
            return false;
        }

        return true;
    }

    public function incrementRegistrations(): void
    {
        $this->increment('current_registrations');
    }

    public function decrementRegistrations(): void
    {
        if ($this->current_registrations > 0) {
            $this->decrement('current_registrations');
        }
    }
}

