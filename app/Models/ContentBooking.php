<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ContentBooking extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'public_id',
        'booking_slot_id',
        'customer_id',
        'client_name',
        'client_email',
        'client_phone',
        'client_instagram',
        'notes',
        'shoot_location',
        'amount',
        'currency',
        'status',
        'paid_at',
        'payment_provider',
        'mercadopago_preference_id',
        'mercadopago_payment_id',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_status',
        'google_calendar_event_id',
        'mercadopago_status',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'analytics_visitor_id',
        'analytics_session_id',
        'fbp',
        'fbc',
        'referrer',
        'landing_url',
        'metadata',
        'admin_notes',
        'deliverables_ready_at',
        'deliverables_drive_url',
    ];

    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'deliverables_ready_at' => 'datetime',
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(BookingSlot::class, 'booking_slot_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliverableLinks(): HasMany
    {
        return $this->hasMany(ContentBookingDeliverableLink::class)->latest();
    }

    public function hasPublishedDeliverables(): bool
    {
        if ($this->relationLoaded('deliverableLinks')) {
            return $this->deliverableLinks->isNotEmpty();
        }

        return $this->deliverableLinks()->exists();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('deliverables')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/jpg',
                'video/mp4',
                'video/quicktime',
                'video/webm',
                'application/pdf',
                'application/zip',
                'application/x-zip-compressed',
                'application/octet-stream',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (! $media || ! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 720, 720)
            ->format('jpg')
            ->quality(86)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1800, 1400)
            ->format('jpg')
            ->quality(90)
            ->nonQueued();
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending_payment', 'pending']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_payment' => 'Pendiente de pago',
            'confirmed' => 'Confirmada',
            'pending' => 'Pago en revisión',
            'failed' => 'Pago fallido',
            'cancelled' => 'Cancelada',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'confirmed' => 'success',
            'pending_payment', 'pending' => 'warning',
            'failed', 'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$'.number_format($this->amount, 0, '.', ',').' '.$this->currency;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        if ($this->payment_provider === 'stripe') {
            return match ($this->stripe_status) {
                'succeeded', 'paid' => 'Pagado',
                'processing', 'requires_action' => 'En revisión',
                'canceled', 'requires_payment_method' => 'No aprobado',
                default => $this->status_label,
            };
        }

        return match ($this->mercadopago_status) {
            'approved' => 'Pagado',
            'pending', 'in_process', 'authorized' => 'En revisión',
            'rejected', 'cancelled', 'refunded', 'charged_back' => 'No aprobado',
            default => $this->status_label,
        };
    }

    public function getSlotSummaryAttribute(): string
    {
        if (! $this->slot) {
            return 'Sin horario asignado';
        }

        return $this->slot->date->translatedFormat('d \d\e F, Y').' — '.$this->slot->time_label;
    }

    public function getDeliverablesCountAttribute(): int
    {
        return $this->getMedia('deliverables')->count();
    }
}
