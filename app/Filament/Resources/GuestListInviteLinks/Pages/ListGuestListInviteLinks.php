<?php

namespace App\Filament\Resources\GuestListInviteLinks\Pages;

use App\Filament\Resources\GuestListInviteLinks\GuestListInviteLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuestListInviteLinks extends ListRecords
{
    protected static string $resource = GuestListInviteLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
