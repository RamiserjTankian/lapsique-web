<?php

namespace App\Filament\Resources\ContactLogs\Pages;

use App\Filament\Resources\ContactLogs\ContactLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContactLog extends EditRecord
{
    protected static string $resource = ContactLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
