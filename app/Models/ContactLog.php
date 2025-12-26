<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'event_id',
        'campaign_id',
        'automation_id',
        'channel',
        'type',
        'subject',
        'message',
        'metadata',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'failed_at',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    // Relaciones
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function emailTracking(): BelongsTo
    {
        return $this->belongsTo(EmailTracking::class);
    }

    // Métodos de utilidad
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function markAsOpened(): void
    {
        $this->update([
            'status' => 'opened',
            'opened_at' => $this->opened_at ?? now(),
        ]);
        
        $this->customer->updateLastInteraction();
    }

    public function markAsClicked(): void
    {
        $this->update([
            'status' => 'clicked',
            'clicked_at' => $this->clicked_at ?? now(),
        ]);
        
        $this->customer->updateLastInteraction();
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $error,
        ]);
    }

    public function markAsBounced(): void
    {
        $this->update([
            'status' => 'bounced',
            'failed_at' => now(),
        ]);
    }

    // Scopes
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['sent', 'delivered', 'opened', 'clicked']);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'bounced']);
    }

    public function scopeOpened($query)
    {
        return $query->whereNotNull('opened_at');
    }

    public function scopeClicked($query)
    {
        return $query->whereNotNull('clicked_at');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
