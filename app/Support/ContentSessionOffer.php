<?php

namespace App\Support;

class ContentSessionOffer
{
    public static function reelDurationSeconds(): int
    {
        return (int) config('booking.content_reel_duration_seconds', 30);
    }

    public static function droneShots(): int
    {
        return (int) config('booking.content_drone_shots', 3);
    }

    public static function photosCount(): int
    {
        return (int) config('booking.content_photos_count', 10);
    }

    public static function translationReplacements(): array
    {
        return [
            'seconds' => self::reelDurationSeconds(),
            'drone_shots' => self::droneShots(),
            'photos_count' => self::photosCount(),
        ];
    }

    public static function description(): string
    {
        return __('funnel.offer.description', self::translationReplacements());
    }

    public static function stripeProductName(): string
    {
        return sprintf(
            'Reel de %ds · cámara Sony + %d Tomas DJI + 10 Fotos',
            self::reelDurationSeconds(),
            self::droneShots(),
        );
    }

    public static function defaultSubtitle(): string
    {
        return __('funnel.offer.default_subtitle', self::translationReplacements());
    }
}
