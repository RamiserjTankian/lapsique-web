<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestListScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_list_entry_id',
        'user_id',
        'scan_status',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function guestListEntry(): BelongsTo
    {
        return $this->belongsTo(GuestListEntry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
