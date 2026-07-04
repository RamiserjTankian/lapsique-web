<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Filament\Resources\SalesAnalytics\SalesAnalyticsResource;
use App\Filament\Resources\SessionCustomers\SessionCustomerResource;
use App\Filament\Resources\SalesAnalytics\Widgets\SalesOverviewWidget;
use App\Filament\Widgets\ContentBookingSalesOrdersTableWidget;
use App\Filament\Widgets\ContentBookingSalesOverviewWidget;
use App\Filament\Widgets\ContentBookingSalesTimelineWidget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard;
use UnitEnum;

class SalesHub extends Dashboard
{
    protected static string $routePath = 'sales';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $title = 'Ventas';

    protected static UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 1;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewAllEvents')
                ->label('Ver detalle por evento')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(SalesAnalyticsResource::getUrl('index')),
            Action::make('viewSessionBookings')
                ->label('Ver reservas de sesiones')
                ->icon('heroicon-o-calendar-days')
                ->url(ContentBookingResource::getUrl('index')),
            Action::make('viewSessionCustomers')
                ->label('Ver clientes de sesiones')
                ->icon('heroicon-o-user-group')
                ->url(SessionCustomerResource::getUrl('index')),
        ];
    }

    public function getWidgets(): array
    {
        return [
            SalesOverviewWidget::class,
            ContentBookingSalesOverviewWidget::class,
            ContentBookingSalesTimelineWidget::class,
            ContentBookingSalesOrdersTableWidget::class,
        ];
    }
}
