<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentGatewayConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'status',
        'account_id',
        'account_email',
        'account_name',
        'access_token',
        'refresh_token',
        'public_key',
        'token_type',
        'scope',
        'expires_at',
        'connected_at',
        'last_synced_at',
        'last_error_at',
        'last_error_message',
        'metadata',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'public_key' => 'encrypted',
        'expires_at' => 'datetime',
        'connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'last_error_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function isConnected(): bool
    {
        if ($this->status !== 'connected') {
            return false;
        }

        return $this->hasDecryptableAccessToken();
    }

    public function hasDecryptableAccessToken(): bool
    {
        if (blank($this->getRawOriginal('access_token'))) {
            return false;
        }

        try {
            return filled($this->access_token);
        } catch (DecryptException $e) {
            Log::warning('Payment gateway token could not be decrypted', [
                'provider' => $this->provider,
                'connection_id' => $this->id,
            ]);

            return false;
        }
    }

    public function isExpired(?Carbon $reference = null): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        $reference = $reference ?? now();

        return $this->expires_at->lte($reference->copy()->addMinutes(5));
    }
}
