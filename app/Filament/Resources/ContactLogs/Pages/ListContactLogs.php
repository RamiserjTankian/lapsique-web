<?php

namespace App\Filament\Resources\ContactLogs\Pages;

use App\Filament\Resources\ContactLogs\ContactLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactLogs extends ListRecords
{
    protected static string $resource = ContactLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
