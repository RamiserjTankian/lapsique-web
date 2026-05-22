<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class StripeSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'secret_key',
        'publishable_key',
        'webhook_secret',
        'currency',
        'webhook_tolerance_seconds',
        'connection_status',
        'last_verified_at',
        'last_error_message',
        'last_verification',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'secret_key' => 'encrypted',
        'publishable_key' => 'encrypted',
        'webhook_secret' => 'encrypted',
        'webhook_tolerance_seconds' => 'integer',
        'last_verified_at' => 'datetime',
        'last_verification' => 'array',
    ];

    public static function tableExists(): bool
    {
        return Schema::hasTable('stripe_settings');
    }

    public static function current(): ?self
    {
        if (! static::tableExists()) {
            return null;
        }

        return static::query()->first();
    }

    public static function currentOrCreate(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => true,
            'currency' => (string) config('stripe.currency', 'MXN'),
            'webhook_tolerance_seconds' => (int) config('stripe.webhook_tolerance_seconds', 300),
            'connection_status' => 'unknown',
        ]);
    }

    public function isActive(): bool
    {
        return $this->is_enabled && filled($this->secret_key);
    }

    public function maskedSecretKey(): ?string
    {
        if (! filled($this->secret_key)) {
            return null;
        }

        $key = (string) $this->secret_key;

        if (strlen($key) <= 12) {
            return str_repeat('•', strlen($key));
        }

        return substr($key, 0, 7).'…'.substr($key, -4);
    }

    public function connectionStatusLabel(): string
    {
        return match ($this->connection_status) {
            'connected' => 'Conectado',
            'misconfigured' => 'Incompleto',
            'error' => 'Error',
            'disabled' => 'Desactivado',
            default => 'Sin verificar',
        };
    }
}
