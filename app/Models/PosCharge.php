<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosCharge extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'customer_event_balance_id',
        'customer_id',
        'event_id',
        'ticket_attendee_id',
        'user_id',
        'ayb_product_id',
        'item_key',
        'item_name',
        'item_type',
        'currency',
        'quantity',
        'unit_price',
        'total',
        'balance_before',
        'balance_after',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function balance(): BelongsTo
    {
        return $this->belongsTo(CustomerEventBalance::class, 'customer_event_balance_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function aybProduct(): BelongsTo
    {
        return $this->belongsTo(AybProduct::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(TicketAttendee::class, 'ticket_attendee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
