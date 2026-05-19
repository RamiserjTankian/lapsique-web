<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAvailabilityRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'time_label',
        'time_value',
        'max_bookings',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'max_bookings' => 'integer',
        'is_active' => 'boolean',
    ];

    // ISO week day names (1=Monday ... 7=Sunday)
    public static array $dayNames = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public static array $dayOptions = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public function getDayNameAttribute(): string
    {
        return static::$dayNames[$this->day_of_week] ?? "Día {$this->day_of_week}";
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
