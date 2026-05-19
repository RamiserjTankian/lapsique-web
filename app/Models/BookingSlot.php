<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time_label',
        'time_value',
        'max_bookings',
        'booked_count',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
        'max_bookings' => 'integer',
        'booked_count' => 'integer',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(ContentBooking::class, 'booking_slot_id');
    }

    public function isAvailable(): bool
    {
        return $this->is_active && $this->booked_count < $this->max_bookings;
    }

    public function getRemainingAttribute(): int
    {
        return max(0, $this->max_bookings - $this->booked_count);
    }

    public function scopeAvailable($query)
    {
        return $query
            ->where('is_active', true)
            ->where('date', '>=', today())
            ->whereColumn('booked_count', '<', 'max_bookings');
    }
}
