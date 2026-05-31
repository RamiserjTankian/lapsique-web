<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'visitor_id',
        'user_id',
        'customer_id',
        'ip_address',
        'ip_hash',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'language',
        'referrer',
        'referrer_domain',
        'landing_url',
        'landing_path',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'source_type',
        'source_label',
        'country',
        'country_name',
        'region',
        'region_code',
        'city',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function pageviews(): HasMany
    {
        return $this->hasMany(AnalyticsPageview::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
