<?php

namespace App\Http\Resources;

use App\Models\ContentBooking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContentBooking */
class ContentBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'client_name' => $this->client_name,
            'client_email' => $this->client_email,
            'client_phone' => $this->client_phone,
            'amount' => (float) $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'currency' => $this->currency,
            'payment_provider' => $this->payment_provider,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'slot_summary' => $this->slot_summary,
            'deliverables_ready_at' => $this->deliverables_ready_at?->toIso8601String(),
            'deliverables_drive_url' => $this->when(
                $this->hasPublishedDeliverables() && filled($this->deliverables_drive_url),
                $this->deliverables_drive_url,
            ),
            'deliverable_links' => $this->when(
                $this->hasPublishedDeliverables(),
                function () {
                    $links = $this->relationLoaded('deliverableLinks')
                        ? $this->deliverableLinks
                        : $this->deliverableLinks()->get();

                    return $links->map(fn ($link) => [
                        'id' => $link->id,
                        'label' => $link->displayLabel(),
                        'url' => $link->url,
                        'created_at' => $link->created_at?->toIso8601String(),
                    ])->values();
                },
            ),
            'is_test_booking' => (bool) data_get($this->metadata, 'skip_payment_mode', false),
            'slot' => $this->slot ? [
                'date' => $this->slot->date->format('Y-m-d'),
                'time_label' => $this->slot->time_label,
            ] : null,
        ];
    }
}
