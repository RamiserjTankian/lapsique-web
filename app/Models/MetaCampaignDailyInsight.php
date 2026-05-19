<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaCampaignDailyInsight extends Model
{
    protected $fillable = [
        'date',
        'campaign_id',
        'campaign_name',
        'spend',
        'impressions',
        'clicks',
        'reach',
        'cpc',
        'cpm',
        'actions',
        'cost_per_action_type',
        'synced_at',
    ];

    protected $casts = [
        'date' => 'date',
        'spend' => 'decimal:2',
        'cpc' => 'decimal:4',
        'cpm' => 'decimal:4',
        'actions' => 'array',
        'cost_per_action_type' => 'array',
        'synced_at' => 'datetime',
    ];
}
