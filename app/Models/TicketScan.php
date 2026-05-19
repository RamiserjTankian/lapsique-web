<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_attendee_id',
        'user_id',
        'scan_status',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(TicketAttendee::class, 'ticket_attendee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
