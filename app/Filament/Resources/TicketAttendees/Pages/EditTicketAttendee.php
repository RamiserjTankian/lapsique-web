<?php

namespace App\Filament\Resources\TicketAttendees\Pages;

use App\Filament\Resources\TicketAttendees\TicketAttendeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTicketAttendee extends EditRecord
{
    protected static string $resource = TicketAttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
