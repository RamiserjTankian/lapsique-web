<?php

namespace App\Filament\Resources\BookingLandingAnalytics\Widgets;

use App\Filament\Resources\BookingLandingAnalytics\Pages\ListBookingLandingAnalytics;
use App\Services\BookingLandingAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingLandingOverviewWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getTablePage(): string
    {
        return ListBookingLandingAnalytics::class;
    }

    protected function getStats(): array
    {
        $snapshot = app(BookingLandingAnalyticsService::class)->snapshotForQuery($this->getPageTableQuery());
        $stats = $snapshot['stats'];

        return [
            Stat::make('Sesiones landing', number_format((int) $stats['sessions']))
                ->description(number_format((int) $stats['unique_visitors']) . ' visitantes únicos')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Reels interactuados', number_format((int) ($stats['reel_engaged'] ?? 0)))
                ->description(number_format((int) ($stats['reel_watch'] ?? 0)) . ' con progreso de video')
                ->descriptionIcon('heroicon-m-play')
                ->color('info'),
            Stat::make('Popup visto', number_format((int) $stats['popup_shown']))
                ->description(number_format((float) $stats['popup_rate'], 2) . '% de sesiones')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),
            Stat::make('CTA popup', number_format((int) $stats['popup_cta']))
                ->description(number_format((float) $stats['popup_click_rate'], 2) . '% sobre popup')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('warning'),
            Stat::make('Form enviados', number_format((int) $stats['submitted']))
                ->description(number_format((int) $stats['date_selected']) . ' eligieron fecha')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success'),
            Stat::make('Reservas confirmadas', number_format((int) $stats['confirmed']))
                ->description(number_format((float) $stats['conversion_rate'], 2) . '% conversión')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Pendiente / fallido', number_format((int) $stats['pending']) . ' / ' . number_format((int) $stats['failed']))
                ->description('Estados posteriores al envío')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('gray'),
        ];
    }
}
