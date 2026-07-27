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

    public const SERVICE_ELECTRONIC_EVENT_COVERAGE = 'electronic_event_coverage';

    public const SERVICE_DRONE_SESSION = 'drone_session';

    public const SERVICE_CONSTRUCTION_PROGRESS = 'construction_progress';

    public const SERVICE_MULTI_CAMERA = 'multi_camera';

    /**
     * @return array<string, string>
     */
    public static function serviceOptions(): array
    {
        return [
            self::SERVICE_CONTENT_SESSION => 'Sesión de contenido',
            self::SERVICE_DJ_SET => 'DJ Set',
            self::SERVICE_ELECTRONIC_EVENT_COVERAGE => 'Cobertura de evento electrónico',
            self::SERVICE_DRONE_SESSION => 'Vuelo con dron',
            self::SERVICE_CONSTRUCTION_PROGRESS => 'Avance de obra',
            self::SERVICE_MULTI_CAMERA => 'Producción multicámara',
        ];
    }

    public static function amountForService(string $serviceType): int
    {
        return match ($serviceType) {
            self::SERVICE_DJ_SET => (int) config('booking.dj_set_price', 10000),
            self::SERVICE_ELECTRONIC_EVENT_COVERAGE => (int) config('booking.electronic_event_coverage_price', 4500),
            self::SERVICE_DRONE_SESSION => (int) config('booking.drone_session_price', 3000),
            self::SERVICE_CONSTRUCTION_PROGRESS => (int) config('booking.construction_progress_price', 5000),
            self::SERVICE_MULTI_CAMERA => (int) config('booking.multi_camera_price', 5000),
            default => SiteSetting::current()?->booking_price ?: (int) config('booking.content_price', 3000),
        };
    }

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

    public function isElectronicEventCoverage(): bool
    {
        return $this->service_type === self::SERVICE_ELECTRONIC_EVENT_COVERAGE;
    }

    public function isConstructionProgress(): bool
    {
        return $this->service_type === self::SERVICE_CONSTRUCTION_PROGRESS;
    }

    public function isMultiCamera(): bool
    {
        return $this->service_type === self::SERVICE_MULTI_CAMERA;
    }

    public function getServiceNameAttribute(): string
    {
        return match ($this->service_type) {
            self::SERVICE_DJ_SET => 'Grabación de DJ Set',
            self::SERVICE_ELECTRONIC_EVENT_COVERAGE => 'Cobertura audiovisual de evento electrónico',
            self::SERVICE_DRONE_SESSION => 'Sesión de vuelo con dron',
            self::SERVICE_CONSTRUCTION_PROGRESS => 'Avance de obra con dron',
            self::SERVICE_MULTI_CAMERA => 'Producción multicámara de DJ set',
            default => 'Sesión de contenido',
        };
    }

    public function getServiceShortNameAttribute(): string
    {
        return match ($this->service_type) {
            self::SERVICE_DJ_SET => 'DJ Set',
            self::SERVICE_ELECTRONIC_EVENT_COVERAGE => 'Cobertura de evento',
            self::SERVICE_DRONE_SESSION => 'Vuelo con dron',
            self::SERVICE_CONSTRUCTION_PROGRESS => 'Avance de obra',
            self::SERVICE_MULTI_CAMERA => 'Multicámara',
            default => 'Sesión de contenido',
        };
    }

    public function getServiceDescriptionAttribute(): string
    {
        return match ($this->service_type) {
            self::SERVICE_DJ_SET => '2 horas de grabación editadas al beat con 2 cámaras fijas, 1 cámara móvil Ronin, dron y audio 32-bit',
            self::SERVICE_ELECTRONIC_EVENT_COVERAGE => 'Cobertura audiovisual con aftermovie, tomas aéreas con dron cuando la operación sea viable y 30 fotografías editadas desde distintos ángulos',
            self::SERVICE_DRONE_SESSION => '1 hora de vuelo con DJI Air 3 para capturar 15 tomas aéreas de hasta 30 seg y hasta 10 fotos en Rec.709 y D-Log',
            self::SERVICE_CONSTRUCTION_PROGRESS => '1 hora de vuelo con DJI Air 3 para documentar avances de obra, contexto, accesos y escala del desarrollo',
            self::SERVICE_MULTI_CAMERA => '10 drops multicámara con 1:30 horas de video continuo en Log, 3 cámaras Sony, audio Zoom H4 a 32 bits y 15 fotografías editadas',
            default => ContentSessionOffer::description(),
        };
    }

    public function getServiceStripeNameAttribute(): string
    {
        return match ($this->service_type) {
            self::SERVICE_DJ_SET => 'Grabación de DJ Set — multicámara, Ronin, dron y audio 32-bit',
            self::SERVICE_ELECTRONIC_EVENT_COVERAGE => 'Cobertura de evento electrónico — aftermovie, dron y 30 fotos desde distintos ángulos',
            self::SERVICE_DRONE_SESSION => 'Sesión de vuelo con dron DJI Air 3 — 15 tomas de hasta 30 seg + 10 fotos',
            self::SERVICE_CONSTRUCTION_PROGRESS => 'Avance de obra con dron DJI Air 3 — reporte visual de progreso',
            self::SERVICE_MULTI_CAMERA => 'Producción multicámara de DJ set — 10 drops, video continuo Log, audio Zoom H4 32-bit y 15 fotos',
            default => ContentSessionOffer::stripeProductName(),
        };
    }

    public function getServiceCalendarSummaryAttribute(): string
    {
        $prefix = match ($this->service_type) {
            self::SERVICE_DJ_SET => '🎧 Grabación de DJ Set',
            self::SERVICE_ELECTRONIC_EVENT_COVERAGE => '🎬 Cobertura de evento electrónico',
            self::SERVICE_DRONE_SESSION => '🚁 Vuelo con dron',
            self::SERVICE_CONSTRUCTION_PROGRESS => '🏗️ Avance de obra con dron',
            self::SERVICE_MULTI_CAMERA => '🎥 Producción multicámara',
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
