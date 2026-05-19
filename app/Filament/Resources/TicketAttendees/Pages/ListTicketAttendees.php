<?php

namespace App\Filament\Resources\TicketAttendees\Pages;

use App\Filament\Resources\TicketAttendees\TicketAttendeeResource;
use Filament\Resources\Pages\ListRecords;

class ListTicketAttendees extends ListRecords
{
    protected static string $resource = TicketAttendeeResource::class;
}
