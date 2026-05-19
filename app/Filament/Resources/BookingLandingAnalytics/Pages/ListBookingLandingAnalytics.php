<?php

namespace App\Filament\Resources\BookingLandingAnalytics\Pages;

use App\Filament\Resources\BookingLandingAnalytics\BookingLandingAnalyticsResource;
use App\Filament\Resources\BookingLandingAnalytics\Widgets\BookingLandingDropoffWidget;
use App\Filament\Resources\BookingLandingAnalytics\Widgets\BookingLandingFunnelChartWidget;
use App\Filament\Resources\BookingLandingAnalytics\Widgets\BookingLandingOverviewWidget;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListBookingLandingAnalytics extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = BookingLandingAnalyticsResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            BookingLandingOverviewWidget::class,
            BookingLandingFunnelChartWidget::class,
            BookingLandingDropoffWidget::class,
        ];
    }
}
