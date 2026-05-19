<?php

namespace App\Filament\Resources\BookingSlots\Pages;

use App\Filament\Resources\BookingSlots\BookingSlotResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookingSlot extends CreateRecord
{
    protected static string $resource = BookingSlotResource::class;
}
