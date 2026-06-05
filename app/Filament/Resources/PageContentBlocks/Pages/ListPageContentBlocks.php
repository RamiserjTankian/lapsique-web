<?php

namespace App\Filament\Resources\PageContentBlocks\Pages;

use App\Filament\Resources\PageContentBlocks\PageContentBlockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPageContentBlocks extends ListRecords
{
    protected static string $resource = PageContentBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
