<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'whatsapp',
        'instagram_handle',
        'notes',
        'status',
        'source',
        'tags',
        'metadata',
        'subscribed_newsletter',
        'subscribed_sms',
        'subscribed_whatsapp',
        'email_verified_at',
        'phone_verified_at',
        'lifecycle_stage',
        'lead_score',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'ip_address',
        'user_agent',
        'last_interaction_at',
        'password',
        'remember_token',
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'subscribed_newsletter' => 'boolean',
        'subscribed_sms' => 'boolean',
        'subscribed_whatsapp' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_interaction_at' => 'datetime',
        'lead_score' => 'integer',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relaciones
    public function guestListEntries(): HasMany
    {
        return $this->hasMany(GuestListEntry::class);
    }

    public function contactLogs(): HasMany
    {
        return $this->hasMany(ContactLog::class);
    }

    public function emailTrackings(): HasMany
    {
        return $this->hasMany(EmailTracking::class);
    }

    public function rps(): BelongsToMany
    {
        return $this->belongsToMany(Rp::class, 'rp_customer')
            ->withTimestamps();
    }

    public function ticketOrders(): HasMany
    {
        return $this->hasMany(TicketOrder::class);
    }

    public function ticketAttendees(): HasMany
    {
        return $this->hasMany(TicketAttendee::class);
    }

    public function eventBalances(): HasMany
    {
        return $this->hasMany(CustomerEventBalance::class);
    }

    public function contentBookings(): HasMany
    {
        return $this->hasMany(ContentBooking::class);
    }

    // Métodos de utilidad
    public function updateLastInteraction(): void
    {
        $this->update(['last_interaction_at' => now()]);
    }

    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $tags = array_values(array_filter($tags, fn($t) => $t !== $tag));
        $this->update(['tags' => $tags]);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    public function incrementLeadScore(int $points): void
    {
        $this->increment('lead_score', $points);
        $this->updateLifecycleStage();
    }

    public function decrementLeadScore(int $points): void
    {
        $this->decrement('lead_score', $points);
        $this->updateLifecycleStage();
    }

    protected function updateLifecycleStage(): void
    {
        $score = $this->lead_score;
        
        $newStage = match(true) {
            $score >= 100 => 'evangelist',
            $score >= 75 => 'customer',
            $score >= 50 => 'sql',
            $score >= 25 => 'mql',
            $score >= 10 => 'lead',
            default => 'subscriber',
        };

        if ($this->lifecycle_stage !== $newStage) {
            $this->update(['lifecycle_stage' => $newStage]);
        }
    }

    // Scopes
    public function scopeLeads($query)
    {
        return $query->where('status', 'lead');
    }

    public function scopeProspects($query)
    {
        return $query->where('status', 'prospect');
    }

    public function scopeCustomers($query)
    {
        return $query->where('status', 'customer');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'inactive');
    }

    public function scopeSubscribedToNewsletter($query)
    {
        return $query->where('subscribed_newsletter', true);
    }

    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeRecentlyActive($query, int $days = 30)
    {
        return $query->where('last_interaction_at', '>=', now()->subDays($days));
    }
}
