<?php

namespace App\Filament\Resources\GuestListInviteLinks\Pages;

use App\Filament\Resources\GuestListInviteLinks\GuestListInviteLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGuestListInviteLink extends EditRecord
{
    protected static string $resource = GuestListInviteLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
