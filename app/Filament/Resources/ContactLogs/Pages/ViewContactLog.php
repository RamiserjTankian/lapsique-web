<?php

namespace App\Filament\Resources\ContactLogs\Pages;

use App\Filament\Resources\ContactLogs\ContactLogResource;
use App\Filament\Resources\ContactLogs\Schemas\ContactLogInfolist;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewContactLog extends ViewRecord
{
    protected static string $resource = ContactLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return ContactLogInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

