<?php

namespace App\Models;

use App\Support\ContentSessionOffer;
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

    public const SERVICE_CONTENT_SESSION = 'content_session';

    public const SERVICE_DJ_SET = 'dj_set';

    public const SERVICE_DRONE_SESSION = 'drone_session';

    public const SERVICE_CONSTRUCTION_PROGRESS = 'construction_progress';

    protected $fillable = [
        'public_id',
        'booking_slot_id',
        'service_type',
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
        'client_ip_address',
        'client_user_agent',
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

    public function isDjSet(): bool
    {
        return $this->service_type === self::SERVICE_DJ_SET;
    }

    public function isDroneSession(): bool
    {
        return $this->service_type === self::SERVICE_DRONE_SESSION;
    }

    public function isConstructionProgress(): bool
    {
        return $this->service_type === self::SERVICE_CONSTRUCTION_PROGRESS;
    }

    public function getServiceNameAttribute(): string
    {
        return match ($this->service_type) {
            self::SERVICE_DJ_SET => 'Grabación de DJ Set',
            self::SERVICE_DRONE_SESSION => 'Sesión de vuelo con dron',
            self::SERVICE_CONSTRUCTION_PROGRESS => 'Avance de obra con dron',
            default => 'Sesión de contenido',
        };
    }

    public function getServiceShortNameAttribute(): string
    {
        return match ($this->service_type) {
            self::SERVICE_DJ_SET => 'DJ Set',
            self::SERVICE_DRONE_SESSION => 'Vuelo con dron',
            self::SERVICE_CONSTRUCTION_PROGRESS => 'Avance de obra',
            default => 'Sesión de contenido',
        };
    }

    public function getServiceDescriptionAttribute(): string
    {
        return match ($this->service_type) {
            self::SERVICE_DJ_SET => '2 horas de grabación editadas al beat con 2 cámaras fijas, 1 cámara móvil Ronin, dron y audio 32-bit',
            self::SERVICE_DRONE_SESSION => '1 hora de vuelo con DJI Air 3 para capturar 10 tomas aéreas y hasta 10 fotos en Rec.709 y D-Log',
            self::SERVICE_CONSTRUCTION_PROGRESS => '1 hora de vuelo con DJI Air 3 para documentar avances de obra, contexto, accesos y escala del desarrollo',
            default => ContentSessionOffer::description(),
        };
    }

    public function getServiceStripeNameAttribute(): string
    {
        return match ($this->service_type) {
            self::SERVICE_DJ_SET => 'Grabación de DJ Set — multicámara, Ronin, dron y audio 32-bit',
            self::SERVICE_DRONE_SESSION => 'Sesión de vuelo con dron DJI Air 3 — 10 tomas aéreas + 10 fotos',
            self::SERVICE_CONSTRUCTION_PROGRESS => 'Avance de obra con dron DJI Air 3 — reporte visual de progreso',
            default => ContentSessionOffer::stripeProductName(),
        };
    }

    public function getServiceCalendarSummaryAttribute(): string
    {
        $prefix = match ($this->service_type) {
            self::SERVICE_DJ_SET => '🎧 Grabación de DJ Set',
            self::SERVICE_DRONE_SESSION => '🚁 Vuelo con dron',
            self::SERVICE_CONSTRUCTION_PROGRESS => '🏗️ Avance de obra con dron',
            default => '📸 Sesión de Contenido',
        };

        return $prefix.' — '.$this->client_name;
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
