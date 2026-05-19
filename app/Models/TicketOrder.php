<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class TicketOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'event_id',
        'customer_id',
        'rp_id',
        'invite_link_id',
        'status',
        'payment_provider',
        'currency',
        'subtotal',
        'fee',
        'total',
        'items_quantity',
        'attendees_expected',
        'attendees_registered',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'buyer_whatsapp',
        'buyer_instagram',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'ip_address',
        'user_agent',
        'mp_preference_id',
        'mp_payment_id',
        'mp_status',
        'mp_status_detail',
        'mp_payment_method',
        'mp_merchant_order_id',
        'mp_external_reference',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'stripe_status',
        'stripe_payment_method',
        'paid_at',
        'cancelled_at',
        'failed_at',
        'refunded_at',
        'metadata',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'fee' => 'decimal:2',
        'total' => 'decimal:2',
        'items_quantity' => 'integer',
        'attendees_expected' => 'integer',
        'attendees_registered' => 'integer',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (! $order->public_id) {
                $order->public_id = (string) Str::uuid();
            }
        });
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

    public function items(): HasMany
    {
        return $this->hasMany(TicketOrderItem::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(TicketAttendee::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function getManageUrl(): string
    {
        return URL::temporarySignedRoute(
            'tickets.manage',
            now()->addDays(30),
            ['order' => $this]
        );
    }

    public function markAsPaid(array $payload = []): void
    {
        $this->fill(array_merge([
            'status' => 'paid',
            'paid_at' => $this->paid_at ?? now(),
        ], $payload));

        $this->save();
    }

    public function markAsPending(array $payload = []): void
    {
        $this->fill(array_merge([
            'status' => 'pending',
        ], $payload));

        $this->save();
    }

    public function markAsFailed(?string $reason = null, array $payload = []): void
    {
        $metadata = $this->metadata ?? [];
        if ($reason) {
            $metadata['failure_reason'] = $reason;
        }

        $this->fill(array_merge([
            'status' => 'failed',
            'failed_at' => now(),
            'metadata' => $metadata,
        ], $payload));

        $this->save();
    }

    public function markAsCancelled(array $payload = []): void
    {
        $this->fill(array_merge([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ], $payload));

        $this->save();
    }

    public function markAsRefunded(array $payload = []): void
    {
        $this->fill(array_merge([
            'status' => 'refunded',
            'refunded_at' => now(),
        ], $payload));

        $this->save();
    }
}
