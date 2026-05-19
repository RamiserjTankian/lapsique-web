<?php

namespace App\Filament\Resources\ContentBookings\Pages;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use Filament\Resources\Pages\ListRecords;

class ListContentBookings extends ListRecords
{
    protected static string $resource = ContentBookingResource::class;
}
