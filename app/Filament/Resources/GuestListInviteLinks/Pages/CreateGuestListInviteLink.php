<?php

namespace App\Filament\Resources\GuestListInviteLinks\Pages;

use App\Filament\Resources\GuestListInviteLinks\GuestListInviteLinkResource;
use App\Models\GuestListInviteLink;
use Filament\Resources\Pages\CreateRecord;

class CreateGuestListInviteLink extends CreateRecord
{
    protected static string $resource = GuestListInviteLinkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generar token automáticamente si no se proporciona
        if (empty($data['token'])) {
            $data['token'] = GuestListInviteLink::generateToken();
        }

        return $data;
    }
}
