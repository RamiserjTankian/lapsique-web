<?php

namespace App\Filament\Resources\PageContentBlocks\Pages;

use App\Filament\Resources\PageContentBlocks\PageContentBlockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPageContentBlock extends EditRecord
{
    protected static string $resource = PageContentBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
