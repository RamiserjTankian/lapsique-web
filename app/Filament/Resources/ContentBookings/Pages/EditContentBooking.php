<?php

namespace App\Filament\Resources\ContentBookings\Pages;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContentBooking extends EditRecord
{
    protected static string $resource = ContentBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
