<?php

namespace App\Filament\Resources\BookingSlots\Pages;

use App\Filament\Resources\BookingSlots\BookingSlotResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBookingSlot extends EditRecord
{
    protected static string $resource = BookingSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
