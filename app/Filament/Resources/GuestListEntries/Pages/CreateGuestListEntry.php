<?php

namespace App\Filament\Resources\GuestListEntries\Pages;

use App\Filament\Resources\GuestListEntries\GuestListEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuestListEntry extends CreateRecord
{
    protected static string $resource = GuestListEntryResource::class;
}
