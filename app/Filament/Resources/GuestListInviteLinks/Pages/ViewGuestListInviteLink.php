<?php

namespace App\Filament\Resources\GuestListInviteLinks\Pages;

use App\Filament\Resources\GuestListInviteLinks\GuestListInviteLinkResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGuestListInviteLink extends ViewRecord
{
    protected static string $resource = GuestListInviteLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
