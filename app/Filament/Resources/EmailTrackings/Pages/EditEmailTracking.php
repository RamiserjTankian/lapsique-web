<?php

namespace App\Filament\Resources\EmailTrackings\Pages;

use App\Filament\Resources\EmailTrackings\EmailTrackingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmailTracking extends EditRecord
{
    protected static string $resource = EmailTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
