<?php

namespace App\Filament\Resources\GuestListEntries\Pages;

use App\Filament\Resources\GuestListEntries\GuestListEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuestListEntries extends ListRecords
{
    protected static string $resource = GuestListEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
