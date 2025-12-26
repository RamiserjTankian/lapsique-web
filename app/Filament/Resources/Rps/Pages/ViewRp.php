<?php

namespace App\Filament\Resources\Rps\Pages;

use App\Filament\Resources\Rps\RpResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRp extends ViewRecord
{
    protected static string $resource = RpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
