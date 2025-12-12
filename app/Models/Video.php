<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'youtube_id',
        'youtube_url',
        'thumbnail_url',
        'location',
        'maps_url',
        'description',
        'published_at',
        'is_featured',
        'priority',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'priority' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
