<?php

namespace App\Filament\Resources\TicketOrders\Pages;

use App\Filament\Resources\TicketOrders\Schemas\TicketOrderInfolist;
use App\Filament\Resources\TicketOrders\TicketOrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewTicketOrder extends ViewRecord
{
    protected static string $resource = TicketOrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return TicketOrderInfolist::configure($schema);
    }
}
