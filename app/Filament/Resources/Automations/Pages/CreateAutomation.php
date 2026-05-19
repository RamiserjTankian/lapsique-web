<?php

namespace App\Filament\Resources\Automations\Pages;

use App\Filament\Resources\Automations\AutomationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAutomation extends CreateRecord
{
    protected static string $resource = AutomationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
