<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookEvent extends Model
{
    protected $fillable = [
        'event_id',
        'type',
        'payload',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public static function alreadyProcessed(string $eventId): bool
    {
        return static::query()
            ->where('event_id', $eventId)
            ->whereNotNull('processed_at')
            ->exists();
    }

    public static function recordReceived(string $eventId, string $type, array $payload): self
    {
        return static::query()->firstOrCreate(
            ['event_id' => $eventId],
            [
                'type' => $type,
                'payload' => $payload,
            ],
        );
    }

    public function markProcessed(): void
    {
        $this->update(['processed_at' => now()]);
    }
}
