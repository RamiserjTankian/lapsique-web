<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsVisitorIdentity extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'customer_id',
        'source',
        'first_linked_at',
        'last_seen_at',
    ];

    protected $casts = [
        'first_linked_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
