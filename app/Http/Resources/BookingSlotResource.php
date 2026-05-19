<?php

namespace App\Http\Resources;

use App\Models\BookingSlot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BookingSlot */
class BookingSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'time_label' => $this->time_label,
            'time_value' => $this->time_value,
        ];
    }
}
