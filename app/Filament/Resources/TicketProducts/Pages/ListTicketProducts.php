<?php

namespace App\Filament\Resources\TicketProducts\Pages;

use App\Filament\Resources\TicketProducts\TicketProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTicketProducts extends ListRecords
{
    protected static string $resource = TicketProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
