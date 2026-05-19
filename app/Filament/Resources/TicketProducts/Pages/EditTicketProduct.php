<?php

namespace App\Filament\Resources\TicketProducts\Pages;

use App\Filament\Resources\TicketProducts\TicketProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTicketProduct extends EditRecord
{
    protected static string $resource = TicketProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
