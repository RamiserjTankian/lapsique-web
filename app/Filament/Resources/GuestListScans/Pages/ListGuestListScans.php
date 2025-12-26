<?php

namespace App\Filament\Resources\GuestListScans\Pages;

use App\Filament\Resources\GuestListScans\GuestListScanResource;
use Filament\Resources\Pages\ListRecords;

class ListGuestListScans extends ListRecords
{
    protected static string $resource = GuestListScanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
