<?php

namespace App\Filament\Resources\Djs\Pages;

use App\Filament\Resources\Djs\DjResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDjs extends ListRecords
{
    protected static string $resource = DjResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
