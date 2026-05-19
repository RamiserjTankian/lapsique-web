<?php

namespace App\Filament\Resources\TicketProducts\Pages;

use App\Filament\Resources\TicketProducts\TicketProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketProduct extends CreateRecord
{
    protected static string $resource = TicketProductResource::class;
}
