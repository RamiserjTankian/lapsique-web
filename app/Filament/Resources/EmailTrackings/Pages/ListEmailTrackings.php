<?php

namespace App\Filament\Resources\EmailTrackings\Pages;

use App\Filament\Resources\EmailTrackings\EmailTrackingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailTrackings extends ListRecords
{
    protected static string $resource = EmailTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
