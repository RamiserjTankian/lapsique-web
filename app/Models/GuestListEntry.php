<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class GuestListEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'customer_id',
        'dj_id',
        'rp_id',
        'invite_link_id',
        'status',
        'gender',
        'notes',
        'check_in_at',
        'check_in_limit',
        'check_in_count',
        'invited_by',
        'plus_ones',
        'invite_token',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'plus_ones' => 'integer',
        'check_in_limit' => 'integer',
        'check_in_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $entry): void {
            if ($entry->invite_token) {
                return;
            }

            do {
                $token = self::generateInviteToken();
            } while (self::where('invite_token', $token)->exists());

            $entry->invite_token = $token;
        });
    }

    protected static ?bool $supportsCheckInCountersCache = null;

    // Relaciones
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function dj(): BelongsTo
    {
        return $this->belongsTo(Dj::class);
    }

    public function rp(): BelongsTo
    {
        return $this->belongsTo(Rp::class);
    }

    public function inviteLink(): BelongsTo
    {
        return $this->belongsTo(GuestListInviteLink::class);
    }

    /**
     * Alias para guestListInviteLink para compatibilidad con whereBelongsTo
     * que infiere el nombre de la relación basándose en el nombre del modelo
     */
    public function guestListInviteLink(): BelongsTo
    {
        return $this->belongsTo(GuestListInviteLink::class, 'invite_link_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(GuestListScan::class);
    }

    public function latestScan(): HasOne
    {
        return $this->hasOne(GuestListScan::class)->latestOfMany('scanned_at');
    }

    public static function generateInviteToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function supportsCheckInCounters(): bool
    {
        if (self::$supportsCheckInCountersCache !== null) {
            return self::$supportsCheckInCountersCache;
        }

        $table = $this->getTable();

        self::$supportsCheckInCountersCache = Schema::hasColumn($table, 'check_in_limit')
            && Schema::hasColumn($table, 'check_in_count');

        return self::$supportsCheckInCountersCache;
    }

    public function ensureInviteToken(): void
    {
        if ($this->invite_token) {
            return;
        }

        do {
            $token = self::generateInviteToken();
        } while (self::where('invite_token', $token)->exists());

        $this->forceFill(['invite_token' => $token])->save();
    }

    public function getCheckInExpiresAt(): Carbon
    {
        if ($this->event?->starts_at) {
            return $this->event->starts_at->copy()->addHours(24);
        }

        return now()->addDays(7);
    }

    public function getCheckInUrl(): string
    {
        $this->ensureInviteToken();

        return URL::temporarySignedRoute(
            'guestlist.checkin.show',
            $this->getCheckInExpiresAt(),
            ['token' => $this->invite_token]
        );
    }

    public function getCheckInConfirmUrl(): string
    {
        $this->ensureInviteToken();

        return URL::temporarySignedRoute(
            'guestlist.checkin.confirm',
            $this->getCheckInExpiresAt(),
            ['token' => $this->invite_token]
        );
    }

    public function getCheckInQrUrl(): string
    {
        $this->ensureInviteToken();

        return route('guestlist.checkin.qr', ['token' => $this->invite_token]);
    }

    public function getCheckInCode(): string
    {
        $this->ensureInviteToken();

        return strtoupper(substr($this->invite_token, -6));
    }

    // Métodos de utilidad
    public function getCheckInLimit(): int
    {
        if (! $this->supportsCheckInCounters()) {
            return 1;
        }

        $limit = (int) ($this->check_in_limit ?? 1);

        return $limit > 0 ? $limit : 1;
    }

    public function getCheckInCount(): int
    {
        if (! $this->supportsCheckInCounters()) {
            return $this->check_in_at ? 1 : 0;
        }

        $count = (int) ($this->check_in_count ?? 0);

        return $count >= 0 ? $count : 0;
    }

    public function getRemainingCheckIns(): int
    {
        if (! $this->supportsCheckInCounters()) {
            return $this->check_in_at ? 0 : 1;
        }

        return max($this->getCheckInLimit() - $this->getCheckInCount(), 0);
    }

    public function canCheckIn(): bool
    {
        if (! $this->supportsCheckInCounters()) {
            return $this->check_in_at === null;
        }

        return $this->getRemainingCheckIns() > 0;
    }

    public function checkIn(): bool
    {
        if (! $this->supportsCheckInCounters()) {
            if ($this->check_in_at) {
                return false;
            }

            $wasAttended = $this->status === 'attended';

            $this->update([
                'status' => 'attended',
                'check_in_at' => now(),
            ]);

            if ($this->customer && ! $wasAttended) {
                $this->customer->updateLastInteraction();
                $this->customer->incrementLeadScore(20);
            }

            return true;
        }

        if (! $this->canCheckIn()) {
            return false;
        }

        $wasAttended = $this->status === 'attended';
        $nextCount = $this->getCheckInCount() + 1;

        $payload = [
            'check_in_count' => $nextCount,
            'check_in_at' => now(),
        ];

        if (! $wasAttended) {
            $payload['status'] = 'attended';
        }

        $this->update($payload);

        if ($this->customer) {
            // Actualizar última interacción del customer
            $this->customer->updateLastInteraction();
            
            // Incrementar lead score por asistencia
            if (! $wasAttended) {
                $this->customer->incrementLeadScore(20);
            }
        }

        return true;
    }

    public function confirm(): void
    {
        $this->update(['status' => 'confirmed']);
        if ($this->customer) {
            $this->customer->updateLastInteraction();
        }
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function markAsNoShow(): void
    {
        $this->update(['status' => 'no_show']);
        if ($this->customer) {
            // Decrementar lead score por no asistir
            $this->customer->decrementLeadScore(10);
        }
    }

    /**
     * Marcar como fraudulento y eliminar el registro, restando el uso del link
     */
    public function markAsFraudulent(): void
    {
        // Decrementar el contador del link antes de eliminar
        if ($this->inviteLink) {
            $this->inviteLink->decrementRegistrations();
        }
        
        // Eliminar el registro
        $this->delete();
    }

    public function isAttended(): bool
    {
        return $this->status === 'attended';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeNoShow($query)
    {
        return $query->where('status', 'no_show');
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeCheckedIn($query)
    {
        return $query->whereNotNull('check_in_at');
    }
}
