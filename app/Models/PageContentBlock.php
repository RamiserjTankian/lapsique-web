<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'site',
        'page',
        'section',
        'key',
        'locale',
        'eyebrow',
        'title',
        'body',
        'asset_path',
        'cta_label',
        'cta_url',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];
}
