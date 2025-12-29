<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'target_audience',
        'content',
        'starts_at',
        'ends_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'conversion_count',
        'bounced_count',
        'failed_count',
        'metadata',
        'created_by',
    ];

    // Campos virtuales para el formulario
    protected $appends = [
        'email_subject',
        'email_body',
        'button_text',
        'button_url',
        'target_tags',
        'target_lifecycle_stages',
        'target_statuses',
    ];

    protected $casts = [
        'target_audience' => 'array',
        'content' => 'array',
        'metadata' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
        'conversion_count' => 'integer',
        'bounced_count' => 'integer',
        'failed_count' => 'integer',
    ];

    // Relaciones
    public function contactLogs(): HasMany
    {
        return $this->hasMany(ContactLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function emailTrackings(): HasMany
    {
        return $this->hasManyThrough(
            EmailTracking::class,
            ContactLog::class,
            'campaign_id',
            'contact_log_id'
        );
    }

    // Accessors para campos virtuales del formulario
    public function getEmailSubjectAttribute(): ?string
    {
        return $this->content['email']['subject'] ?? null;
    }

    public function getEmailBodyAttribute(): ?string
    {
        return $this->content['email']['body'] ?? null;
    }

    public function getButtonTextAttribute(): ?string
    {
        return $this->content['email']['button_text'] ?? null;
    }

    public function getButtonUrlAttribute(): ?string
    {
        return $this->content['email']['button_url'] ?? null;
    }

    public function getTargetTagsAttribute(): ?array
    {
        return $this->target_audience['tags'] ?? null;
    }

    public function getTargetLifecycleStagesAttribute(): ?array
    {
        return $this->target_audience['lifecycle_stages'] ?? null;
    }

    public function getTargetStatusesAttribute(): ?array
    {
        return $this->target_audience['statuses'] ?? null;
    }


    // Métodos de utilidad
    public function getRecipientsQuery()
    {
        $query = Customer::query()->active();

        if (!$this->target_audience) {
            return $query;
        }

        $filters = $this->target_audience;

        // Filtrar por tags
        if (!empty($filters['tags'])) {
            foreach ($filters['tags'] as $tag) {
                $query->withTag($tag);
            }
        }

        // Filtrar por lifecycle_stage
        if (!empty($filters['lifecycle_stages'])) {
            $query->whereIn('lifecycle_stage', $filters['lifecycle_stages']);
        }

        // Filtrar por status
        if (!empty($filters['statuses'])) {
            $query->whereIn('status', $filters['statuses']);
        }

        // Filtrar por lead_score
        if (isset($filters['min_lead_score'])) {
            $query->where('lead_score', '>=', $filters['min_lead_score']);
        }

        if (isset($filters['max_lead_score'])) {
            $query->where('lead_score', '<=', $filters['max_lead_score']);
        }

        // Filtrar por actividad reciente
        if (isset($filters['last_interaction_days'])) {
            $query->recentlyActive($filters['last_interaction_days']);
        }

        // Filtrar por suscripciones según el tipo de campaña
        if ($this->type === 'email') {
            $query->subscribedToNewsletter();
        } elseif ($this->type === 'sms') {
            $query->where('subscribed_sms', true);
        } elseif ($this->type === 'whatsapp') {
            $query->where('subscribed_whatsapp', true);
        }

        return $query;
    }

    public function incrementSent(): void
    {
        $this->increment('sent_count');
    }

    public function incrementDelivered(): void
    {
        $this->increment('delivered_count');
    }

    public function incrementOpened(): void
    {
        $this->increment('opened_count');
    }

    public function incrementClicked(): void
    {
        $this->increment('clicked_count');
    }

    public function incrementBounced(): void
    {
        $this->increment('bounced_count');
    }

    public function incrementFailed(): void
    {
        $this->increment('failed_count');
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    // Métricas calculadas
    public function getDeliveryRateAttribute(): float
    {
        if ($this->sent_count === 0) {
            return 0.0;
        }

        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    public function getOpenRateAttribute(): float
    {
        if ($this->delivered_count === 0) {
            return 0.0;
        }

        return round(($this->opened_count / $this->delivered_count) * 100, 2);
    }

    public function getClickRateAttribute(): float
    {
        if ($this->delivered_count === 0) {
            return 0.0;
        }

        return round(($this->clicked_count / $this->delivered_count) * 100, 2);
    }

    public function getClickToOpenRateAttribute(): float
    {
        if ($this->opened_count === 0) {
            return 0.0;
        }

        return round(($this->clicked_count / $this->opened_count) * 100, 2);
    }

    public function getBounceRateAttribute(): float
    {
        if ($this->sent_count === 0) {
            return 0.0;
        }

        return round(($this->bounced_count / $this->sent_count) * 100, 2);
    }

    public function getConversionRateAttribute(): float
    {
        if ($this->delivered_count === 0) {
            return 0.0;
        }

        return round(($this->conversion_count / $this->delivered_count) * 100, 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('starts_at', '>', now());
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('starts_at', '<=', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
