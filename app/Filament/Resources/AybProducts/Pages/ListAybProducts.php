<?php

namespace App\Filament\Resources\AybProducts\Pages;

use App\Filament\Resources\AybProducts\AybProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAybProducts extends ListRecords
{
    protected static string $resource = AybProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
