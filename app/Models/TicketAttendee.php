<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class TicketAttendee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'ticket_order_id',
        'ticket_order_item_id',
        'ticket_product_id',
        'event_id',
        'customer_id',
        'rp_id',
        'invite_link_id',
        'status',
        'name',
        'email',
        'whatsapp',
        'instagram_handle',
        'phone',
        'gender',
        'notes',
        'invite_token',
        'check_in_at',
        'check_in_limit',
        'check_in_count',
        'registered_at',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_in_limit' => 'integer',
        'check_in_count' => 'integer',
        'registered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attendee): void {
            if ($attendee->invite_token) {
                return;
            }

            do {
                $token = self::generateInviteToken();
            } while (self::where('invite_token', $token)->exists());

            $attendee->invite_token = $token;
        });
    }

    public static function generateInviteToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class, 'ticket_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(TicketOrderItem::class, 'ticket_order_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(TicketProduct::class, 'ticket_product_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rp(): BelongsTo
    {
        return $this->belongsTo(Rp::class);
    }

    public function inviteLink(): BelongsTo
    {
        return $this->belongsTo(GuestListInviteLink::class, 'invite_link_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(TicketScan::class);
    }

    public function latestScan(): HasOne
    {
        return $this->hasOne(TicketScan::class)->latestOfMany('scanned_at');
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
            'tickets.checkin.show',
            $this->getCheckInExpiresAt(),
            ['token' => $this->invite_token]
        );
    }

    public function getCheckInConfirmUrl(): string
    {
        $this->ensureInviteToken();

        return URL::temporarySignedRoute(
            'tickets.checkin.confirm',
            $this->getCheckInExpiresAt(),
            ['token' => $this->invite_token]
        );
    }

    public function getCheckInQrUrl(): string
    {
        $this->ensureInviteToken();

        return route('tickets.checkin.qr', ['token' => $this->invite_token]);
    }

    public function getCheckInCode(): string
    {
        $this->ensureInviteToken();

        return strtoupper(substr($this->invite_token, -6));
    }

    public function getCheckInLimit(): int
    {
        $limit = (int) ($this->check_in_limit ?? 1);

        return $limit > 0 ? $limit : 1;
    }

    public function getCheckInCount(): int
    {
        $count = (int) ($this->check_in_count ?? 0);

        return $count >= 0 ? $count : 0;
    }

    public function getRemainingCheckIns(): int
    {
        return max($this->getCheckInLimit() - $this->getCheckInCount(), 0);
    }

    public function canCheckIn(): bool
    {
        return $this->getRemainingCheckIns() > 0;
    }

    public function checkIn(): bool
    {
        if (! $this->canCheckIn()) {
            return false;
        }

        $this->update([
            'status' => 'checked_in',
            'check_in_at' => now(),
            'check_in_count' => $this->getCheckInCount() + 1,
        ]);

        if ($this->customer) {
            $this->customer->updateLastInteraction();
            $this->customer->incrementLeadScore(20);
        }

        return true;
    }
}
