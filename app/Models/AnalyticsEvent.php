<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'analytics_session_id',
        'analytics_pageview_id',
        'visitor_id',
        'user_id',
        'name',
        'category',
        'label',
        'value',
        'url',
        'path',
        'element_tag',
        'element_text',
        'element_href',
        'element_id',
        'element_classes',
        'element_target',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AnalyticsSession::class, 'analytics_session_id');
    }

    public function pageview(): BelongsTo
    {
        return $this->belongsTo(AnalyticsPageview::class, 'analytics_pageview_id');
    }
}
