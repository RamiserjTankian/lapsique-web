<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingLandingAnalytics\BookingLandingAnalyticsResource;
use App\Filament\Resources\ContactLogs\ContactLogResource;
use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Widgets\AnalyticsDeviceChartWidget;
use App\Filament\Widgets\AnalyticsStatsWidget;
use App\Filament\Widgets\AnalyticsTopClicksChartWidget;
use App\Filament\Widgets\AnalyticsTopPagesChartWidget;
use App\Filament\Widgets\AnalyticsTopReferrersChartWidget;
use App\Filament\Widgets\AnalyticsTrafficChartWidget;
use App\Filament\Widgets\ContactLogStatusChartWidget;
use App\Filament\Widgets\ContentBookingMenuServicesOverviewWidget;
use App\Filament\Widgets\ContentBookingPipelineTableWidget;
use App\Filament\Widgets\CrmContentStatsWidget;
use App\Filament\Widgets\CrmEventsStatsWidget;
use App\Filament\Widgets\CrmMarketingStatsWidget;
use App\Filament\Widgets\CrmPeopleStatsWidget;
use App\Filament\Widgets\CustomerJourneyFunnelChartWidget;
use App\Filament\Widgets\CustomerJourneySourcesWidget;
use App\Filament\Widgets\CustomerJourneyStatsWidget;
use App\Filament\Widgets\GuestListStatusChartWidget;
use App\Filament\Widgets\HotLeadsTableWidget;
use App\Filament\Widgets\LeadManagementStatsWidget;
use App\Filament\Widgets\RecentContactLogsWidget;
use App\Filament\Widgets\RecentCustomersWidget;
use App\Filament\Widgets\RecentGuestListEntriesWidget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard;
use UnitEnum;

class AdminDashboard extends Dashboard
{
    protected static bool $isDiscovered = false;

    protected static string $routePath = '/';

    protected static ?string $title = 'CRM Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static UnitEnum|string|null $navigationGroup = null;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    public function getColumns(): int|array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('salesHub')
                ->label('Ventas')
                ->icon('heroicon-o-currency-dollar')
                ->url(SalesHub::getUrl()),
            Action::make('createBooking')
                ->label('Nueva reserva')
                ->icon('heroicon-o-calendar-days')
                ->url(ContentBookingResource::getUrl('create')),
            Action::make('leads')
                ->label('Leads / clientes')
                ->icon('heroicon-o-users')
                ->url(CustomerResource::getUrl('index')),
            Action::make('contacts')
                ->label('Contactos')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->url(ContactLogResource::getUrl('index')),
            Action::make('landingAnalytics')
                ->label('Landing analytics')
                ->icon('heroicon-o-chart-bar')
                ->url(BookingLandingAnalyticsResource::getUrl('index')),
        ];
    }

    public function getWidgets(): array
    {
        return [
            CustomerJourneyStatsWidget::class,
            LeadManagementStatsWidget::class,
            ContentBookingMenuServicesOverviewWidget::class,
            ContentBookingPipelineTableWidget::class,
            CustomerJourneyFunnelChartWidget::class,
            CustomerJourneySourcesWidget::class,
            HotLeadsTableWidget::class,
            CrmPeopleStatsWidget::class,
            CrmEventsStatsWidget::class,
            CrmContentStatsWidget::class,
            CrmMarketingStatsWidget::class,
            AnalyticsStatsWidget::class,
            AnalyticsTrafficChartWidget::class,
            ContactLogStatusChartWidget::class,
            GuestListStatusChartWidget::class,
            AnalyticsTopPagesChartWidget::class,
            AnalyticsTopReferrersChartWidget::class,
            AnalyticsDeviceChartWidget::class,
            AnalyticsTopClicksChartWidget::class,
            RecentCustomersWidget::class,
            RecentContactLogsWidget::class,
            RecentGuestListEntriesWidget::class,
        ];
    }
}
