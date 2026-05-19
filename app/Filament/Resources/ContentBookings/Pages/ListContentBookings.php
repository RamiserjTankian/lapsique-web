<?php

namespace App\Filament\Resources\ContentBookings\Pages;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Filament\Widgets\ContentBookingSalesOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListContentBookings extends ListRecords
{
    protected static string $resource = ContentBookingResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ContentBookingSalesOverviewWidget::class,
        ];
    }
}
