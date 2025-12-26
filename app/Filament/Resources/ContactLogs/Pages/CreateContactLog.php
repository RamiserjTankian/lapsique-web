<?php

namespace App\Filament\Resources\ContactLogs\Pages;

use App\Filament\Resources\ContactLogs\ContactLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContactLog extends CreateRecord
{
    protected static string $resource = ContactLogResource::class;
}
