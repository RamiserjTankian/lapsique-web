<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsDeviceChartWidget;
use App\Filament\Widgets\AnalyticsStatsWidget;
use App\Filament\Widgets\AnalyticsTopClicksChartWidget;
use App\Filament\Widgets\AnalyticsTopPagesChartWidget;
use App\Filament\Widgets\AnalyticsTopReferrersChartWidget;
use App\Filament\Widgets\AnalyticsTrafficChartWidget;
use App\Filament\Widgets\ContactLogStatusChartWidget;
use App\Filament\Widgets\CrmContentStatsWidget;
use App\Filament\Widgets\CrmEventsStatsWidget;
use App\Filament\Widgets\CrmMarketingStatsWidget;
use App\Filament\Widgets\CrmPeopleStatsWidget;
use App\Filament\Widgets\CustomerJourneyFunnelChartWidget;
use App\Filament\Widgets\CustomerJourneySourcesWidget;
use App\Filament\Widgets\CustomerJourneyStatsWidget;
use App\Filament\Widgets\GuestListStatusChartWidget;
use App\Filament\Widgets\HotLeadsTableWidget;
use App\Filament\Widgets\RecentContactLogsWidget;
use App\Filament\Widgets\RecentCustomersWidget;
use App\Filament\Widgets\RecentGuestListEntriesWidget;
use BackedEnum;
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

    public function getWidgets(): array
    {
        return [
            CustomerJourneyStatsWidget::class,
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
