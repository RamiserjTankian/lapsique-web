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
        'booking_og_image',
        'djset_og_image',
        'booking_price',
        'booking_whatsapp',
        'booking_team_name',
        'booking_team_bio',
        'google_calendar_id',
        'booking_calendar_notify_email',
        'booking_studio_location',
        'booking_weeks_ahead',
        'booking_availability_days',
        'booking_start_time',
        'booking_end_time',
        'booking_advance_hours',
        'booking_duration_minutes',
        'home_hero_proof_1_title',
        'home_hero_proof_1_source',
        'home_hero_proof_1_reference',
        'home_hero_proof_2_title',
        'home_hero_proof_2_source',
        'home_hero_proof_2_reference',
    ];

    protected $casts = [
        'booking_price' => 'integer',
        'booking_weeks_ahead' => 'integer',
        'booking_availability_days' => 'integer',
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

    public function bookingAvailabilityDays(): int
    {
        return $this->booking_availability_days ?: (int) config('booking.availability_days', 11);
    }

    public function bookingStartTime(): string
    {
        return substr($this->booking_start_time ?: (string) config('booking.default_start_time', '14:00'), 0, 5);
    }

    public function bookingEndTime(): string
    {
        return substr($this->booking_end_time ?: (string) config('booking.default_end_time', '17:00'), 0, 5);
    }

    public function bookingDurationMinutes(): int
    {
        return $this->booking_duration_minutes ?: (int) config('booking.default_duration_minutes', 120);
    }

    /**
     * @return array{title: ?string, source: ?string, reference: ?string}
     */
    public function homeHeroProofSlot(): array
    {
        return [
            'title' => $this->home_hero_proof_1_title,
            'source' => $this->home_hero_proof_1_source,
            'reference' => $this->home_hero_proof_1_reference,
        ];
    }
}
