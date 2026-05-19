<?php

namespace App\Filament\Resources\SalesAnalytics\Pages;

use App\Filament\Resources\SalesAnalytics\SalesAnalyticsResource;
use App\Filament\Resources\SalesAnalytics\Widgets\SalesOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListSalesAnalytics extends ListRecords
{
    protected static string $resource = SalesAnalyticsResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            SalesOverviewWidget::class,
        ];
    }
}
