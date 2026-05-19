<?php

namespace App\Filament\Resources\AybProducts\Pages;

use App\Filament\Resources\AybProducts\AybProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAybProduct extends EditRecord
{
    protected static string $resource = AybProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
