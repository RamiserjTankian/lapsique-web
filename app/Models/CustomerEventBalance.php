<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerEventBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'event_id',
        'last_ticket_order_id',
        'currency',
        'balance',
        'total_credited',
        'total_consumed',
        'metadata',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_credited' => 'decimal:2',
        'total_consumed' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class)->withTrashed();
    }

    public function lastTicketOrder(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class, 'last_ticket_order_id')->withTrashed();
    }

    public function posCharges(): HasMany
    {
        return $this->hasMany(PosCharge::class);
    }
}
