<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Automation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'trigger_config',
        'status',
        'steps',
        'total_triggered',
        'total_completed',
        'total_failed',
        'created_by',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'steps' => 'array',
        'total_triggered' => 'integer',
        'total_completed' => 'integer',
        'total_failed' => 'integer',
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

    // Métodos de utilidad
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    public function pause(): void
    {
        $this->update(['status' => 'paused']);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function incrementTriggered(): void
    {
        $this->increment('total_triggered');
    }

    public function incrementCompleted(): void
    {
        $this->increment('total_completed');
    }

    public function incrementFailed(): void
    {
        $this->increment('total_failed');
    }

    // Métricas calculadas
    public function getCompletionRateAttribute(): float
    {
        if ($this->total_triggered === 0) {
            return 0.0;
        }

        return round(($this->total_completed / $this->total_triggered) * 100, 2);
    }

    public function getFailureRateAttribute(): float
    {
        if ($this->total_triggered === 0) {
            return 0.0;
        }

        return round(($this->total_failed / $this->total_triggered) * 100, 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeByTrigger($query, string $triggerType)
    {
        return $query->where('trigger_type', $triggerType);
    }

    // Métodos para ejecutar la automatización
    public function shouldTriggerFor(Customer $customer, array $context = []): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // Lógica específica según el tipo de trigger
        return match($this->trigger_type) {
            'signup' => $this->checkSignupTrigger($customer, $context),
            'event_registration' => $this->checkEventRegistrationTrigger($customer, $context),
            'event_reminder' => $this->checkEventReminderTrigger($customer, $context),
            'tag_added' => $this->checkTagAddedTrigger($customer, $context),
            'lifecycle_change' => $this->checkLifecycleChangeTrigger($customer, $context),
            'score_threshold' => $this->checkScoreThresholdTrigger($customer, $context),
            default => false,
        };
    }

    protected function checkSignupTrigger(Customer $customer, array $context): bool
    {
        $config = $this->trigger_config ?? [];
        
        // Verificar si el source coincide
        if (!empty($config['sources']) && !in_array($customer->source, $config['sources'])) {
            return false;
        }

        return true;
    }

    protected function checkEventRegistrationTrigger(Customer $customer, array $context): bool
    {
        // Verificar si hay un event_id en el contexto
        return !empty($context['event_id']);
    }

    protected function checkEventReminderTrigger(Customer $customer, array $context): bool
    {
        $config = $this->trigger_config ?? [];
        
        // Verificar que haya un evento y que falte X horas
        if (empty($context['event_id'])) {
            return false;
        }

        $hoursBeforeEvent = $config['hours_before'] ?? 24;
        // Lógica adicional se implementaría en el Job que procesa esto

        return true;
    }

    protected function checkTagAddedTrigger(Customer $customer, array $context): bool
    {
        $config = $this->trigger_config ?? [];
        $requiredTag = $config['tag'] ?? null;

        if (!$requiredTag || empty($context['added_tag'])) {
            return false;
        }

        return $context['added_tag'] === $requiredTag;
    }

    protected function checkLifecycleChangeTrigger(Customer $customer, array $context): bool
    {
        $config = $this->trigger_config ?? [];
        $targetStage = $config['lifecycle_stage'] ?? null;

        if (!$targetStage) {
            return false;
        }

        return $customer->lifecycle_stage === $targetStage;
    }

    protected function checkScoreThresholdTrigger(Customer $customer, array $context): bool
    {
        $config = $this->trigger_config ?? [];
        $threshold = $config['threshold'] ?? 0;
        $operator = $config['operator'] ?? '>=';

        return match($operator) {
            '>=' => $customer->lead_score >= $threshold,
            '>' => $customer->lead_score > $threshold,
            '<=' => $customer->lead_score <= $threshold,
            '<' => $customer->lead_score < $threshold,
            '=' => $customer->lead_score == $threshold,
            default => false,
        };
    }
}
