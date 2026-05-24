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

    public static function description(): string
    {
        return sprintf(
            'Reel de %ds · cámara Sony + %d tomas aéreas con dron DJI + %d fotografías editadas',
            self::reelDurationSeconds(),
            self::droneShots(),
            self::photosCount(),
        );
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
        return sprintf(
            'Reel de %ds con %d tomas aéreas DJI y %d fotos editadas. Producción dirigida con Sony full frame para pauta y redes.',
            self::reelDurationSeconds(),
            self::droneShots(),
            self::photosCount(),
        );
    }
}
