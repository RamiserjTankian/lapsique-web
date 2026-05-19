<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_order_id',
        'ticket_product_id',
        'name',
        'category',
        'quantity',
        'unit_price',
        'total_price',
        'access_units',
        'check_in_limit',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'access_units' => 'integer',
        'check_in_limit' => 'integer',
        'metadata' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class, 'ticket_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(TicketProduct::class, 'ticket_product_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(TicketAttendee::class, 'ticket_order_item_id');
    }
}
