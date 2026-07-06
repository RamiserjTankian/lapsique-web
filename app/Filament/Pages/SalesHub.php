<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingLandingAnalytics\BookingLandingAnalyticsResource;
use App\Filament\Resources\ContactLogs\ContactLogResource;
use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\SalesAnalytics\SalesAnalyticsResource;
use App\Filament\Resources\SalesAnalytics\Widgets\SalesOverviewWidget;
use App\Filament\Resources\SessionCustomers\SessionCustomerResource;
use App\Filament\Widgets\ContentBookingMenuServicesOverviewWidget;
use App\Filament\Widgets\ContentBookingPipelineTableWidget;
use App\Filament\Widgets\ContentBookingSalesOrdersTableWidget;
use App\Filament\Widgets\ContentBookingSalesOverviewWidget;
use App\Filament\Widgets\ContentBookingSalesTimelineWidget;
use App\Filament\Widgets\LeadManagementStatsWidget;
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
            Action::make('viewLeads')
                ->label('Ver leads')
                ->icon('heroicon-o-users')
                ->url(CustomerResource::getUrl('index')),
            Action::make('viewContactLogs')
                ->label('Ver contactos')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->url(ContactLogResource::getUrl('index')),
            Action::make('viewLandingAnalytics')
                ->label('Ver landing analytics')
                ->icon('heroicon-o-chart-bar')
                ->url(BookingLandingAnalyticsResource::getUrl('index')),
        ];
    }

    public function getWidgets(): array
    {
        return [
            SalesOverviewWidget::class,
            LeadManagementStatsWidget::class,
            ContentBookingMenuServicesOverviewWidget::class,
            ContentBookingSalesOverviewWidget::class,
            ContentBookingSalesTimelineWidget::class,
            ContentBookingPipelineTableWidget::class,
            ContentBookingSalesOrdersTableWidget::class,
        ];
    }
}
