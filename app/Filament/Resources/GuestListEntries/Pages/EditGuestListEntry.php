<?php

namespace App\Filament\Resources\GuestListEntries\Pages;

use App\Filament\Resources\GuestListEntries\GuestListEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditGuestListEntry extends EditRecord
{
    protected static string $resource = GuestListEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
