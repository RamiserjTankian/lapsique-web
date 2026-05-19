<?php

namespace App\Filament\Resources\BookingSlots\Pages;

use App\Filament\Resources\BookingSlots\BookingSlotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookingSlots extends ListRecords
{
    protected static string $resource = BookingSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
