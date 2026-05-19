<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBookingDeliverableLink extends Model
{
    protected $fillable = [
        'content_booking_id',
        'label',
        'url',
        'notified_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    public function contentBooking(): BelongsTo
    {
        return $this->belongsTo(ContentBooking::class);
    }

    public function displayLabel(): string
    {
        if (filled($this->label)) {
            return $this->label;
        }

        return 'Material en Google Drive';
    }
}
