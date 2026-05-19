<?php

namespace App\Filament\Resources\BookingLandingAnalytics\Widgets;

use App\Filament\Resources\BookingLandingAnalytics\Pages\ListBookingLandingAnalytics;
use App\Services\BookingLandingAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\Widget;

class BookingLandingDropoffWidget extends Widget
{
    use InteractsWithPageTable;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.resources.booking-landing-analytics.widgets.dropoff-widget';

    protected function getTablePage(): string
    {
        return ListBookingLandingAnalytics::class;
    }

    protected function getViewData(): array
    {
        $snapshot = app(BookingLandingAnalyticsService::class)->snapshotForQuery($this->getPageTableQuery());

        return [
            'sources' => $snapshot['sources'],
            'dropoffs' => $snapshot['dropoffs'],
            'recentSessions' => $snapshot['recent_sessions'],
        ];
    }
}
