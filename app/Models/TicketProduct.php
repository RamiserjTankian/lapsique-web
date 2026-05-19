<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class TicketProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'category',
        'currency',
        'price',
        'service_charge_pct',
        'access_units',
        'check_in_limit',
        'stock',
        'reserved_count',
        'sold_count',
        'max_per_order',
        'starts_at',
        'ends_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'service_charge_pct' => 'decimal:2',
        'access_units' => 'integer',
        'check_in_limit' => 'integer',
        'stock' => 'integer',
        'reserved_count' => 'integer',
        'sold_count' => 'integer',
        'max_per_order' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(TicketOrderItem::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(TicketAttendee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isOnSale(?Carbon $now = null): bool
    {
        $now = $now ?? now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function availableStock(): ?int
    {
        if ($this->stock === null) {
            return null;
        }

        return max($this->stock - $this->reserved_count - $this->sold_count, 0);
    }

    /** Base price (before service charge). Equals price when service_charge_pct is 0. */
    public function getBasePriceAttribute(): float
    {
        if ($this->service_charge_pct <= 0) {
            return (float) $this->price;
        }

        return round((float) $this->price / (1 + $this->service_charge_pct / 100), 2);
    }

    /** Service charge amount. */
    public function getServiceChargeAmountAttribute(): float
    {
        return round((float) $this->price - $this->base_price, 2);
    }

    /** Whether this product has a service charge. */
    public function hasServiceCharge(): bool
    {
        return $this->service_charge_pct > 0;
    }

    public function canReserve(int $quantity): bool
    {
        if ($quantity < 1) {
            return false;
        }

        if (! $this->isOnSale()) {
            return false;
        }

        if ($this->max_per_order && $quantity > $this->max_per_order) {
            return false;
        }

        $available = $this->availableStock();

        return $available === null || $available >= $quantity;
    }
}
