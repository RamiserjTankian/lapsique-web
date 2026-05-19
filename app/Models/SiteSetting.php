<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_pixel_id',
        'booking_title',
        'booking_subtitle',
        'booking_price',
        'booking_whatsapp',
        'google_calendar_id',
        'booking_weeks_ahead',
        'booking_advance_hours',
        'booking_duration_minutes',
    ];

    protected $casts = [
        'booking_price' => 'integer',
        'booking_weeks_ahead' => 'integer',
        'booking_advance_hours' => 'integer',
        'booking_duration_minutes' => 'integer',
    ];

    public static function current(): ?self
    {
        if (! Schema::hasTable('site_settings')) {
            return null;
        }

        return static::query()->first();
    }

    public static function currentOrNew(): self
    {
        return static::query()->firstOrNew();
    }

    public static function metaPixelId(): ?string
    {
        return static::current()?->meta_pixel_id;
    }
}
