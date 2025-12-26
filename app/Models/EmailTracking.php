<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_log_id',
        'customer_id',
        'tracking_token',
        'opens_count',
        'clicks_count',
        'first_opened_at',
        'last_opened_at',
        'first_clicked_at',
        'last_clicked_at',
        'clicked_links',
        'user_agent',
        'ip_address',
        'location',
        'device_type',
    ];

    protected $casts = [
        'clicked_links' => 'array',
        'location' => 'array',
        'first_opened_at' => 'datetime',
        'last_opened_at' => 'datetime',
        'first_clicked_at' => 'datetime',
        'last_clicked_at' => 'datetime',
        'opens_count' => 'integer',
        'clicks_count' => 'integer',
    ];

    // Relaciones
    public function contactLog(): BelongsTo
    {
        return $this->belongsTo(ContactLog::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Métodos de utilidad
    public static function generateToken(): string
    {
        return (string) Str::uuid();
    }

    public function recordOpen(string $ipAddress = null, string $userAgent = null): void
    {
        $data = [
            'opens_count' => $this->opens_count + 1,
            'last_opened_at' => now(),
        ];

        if ($this->opens_count === 0) {
            $data['first_opened_at'] = now();
        }

        if ($ipAddress) {
            $data['ip_address'] = $ipAddress;
        }

        if ($userAgent) {
            $data['user_agent'] = $userAgent;
            $data['device_type'] = $this->detectDeviceType($userAgent);
        }

        $this->update($data);

        // Actualizar el ContactLog
        $this->contactLog->markAsOpened();

        // Incrementar lead score
        if ($this->opens_count === 1) {
            $this->customer->incrementLeadScore(5);
        }
    }

    public function recordClick(string $url, string $ipAddress = null, string $userAgent = null): void
    {
        $clickedLinks = $this->clicked_links ?? [];
        $clickedLinks[] = [
            'url' => $url,
            'clicked_at' => now()->toISOString(),
        ];

        $data = [
            'clicks_count' => $this->clicks_count + 1,
            'last_clicked_at' => now(),
            'clicked_links' => $clickedLinks,
        ];

        if ($this->clicks_count === 0) {
            $data['first_clicked_at'] = now();
        }

        if ($ipAddress) {
            $data['ip_address'] = $ipAddress;
        }

        if ($userAgent) {
            $data['user_agent'] = $userAgent;
            $data['device_type'] = $this->detectDeviceType($userAgent);
        }

        $this->update($data);

        // Actualizar el ContactLog
        $this->contactLog->markAsClicked();

        // Incrementar lead score
        if ($this->clicks_count === 1) {
            $this->customer->incrementLeadScore(10);
        }
    }

    protected function detectDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile/i', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/bot|crawler|spider|scrapy/i', $userAgent)) {
            return 'unknown';
        }

        return 'desktop';
    }

    public function getEngagementRateAttribute(): float
    {
        if ($this->opens_count === 0) {
            return 0.0;
        }

        return round(($this->clicks_count / $this->opens_count) * 100, 2);
    }

    // Scopes
    public function scopeOpened($query)
    {
        return $query->where('opens_count', '>', 0);
    }

    public function scopeClicked($query)
    {
        return $query->where('clicks_count', '>', 0);
    }

    public function scopeByDevice($query, string $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeHighEngagement($query)
    {
        return $query->where('opens_count', '>=', 2)
                     ->orWhere('clicks_count', '>=', 1);
    }
}
