<?php

namespace App\Filament\Widgets;

use App\Support\ContentBookingSalesInsights;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentBookingMenuServicesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return collect(ContentBookingSalesInsights::menuServiceRows())
            ->map(function (array $row): Stat {
                $conversion = $row['conversion_rate'] !== null ? "{$row['conversion_rate']}%" : 'sin conversion';
                $color = match (true) {
                    $row['confirmed'] > 0 => 'success',
                    $row['pending'] > 0 => 'warning',
                    $row['leads'] > 0 || $row['whatsapp'] > 0 => 'info',
                    default => 'gray',
                };

                return Stat::make($row['label'], "{$row['leads']} leads / {$row['bookings']} reservas")
                    ->description("{$row['pageviews']} visitas · {$row['whatsapp']} WhatsApp · {$conversion} · $".number_format($row['revenue'], 0).' MXN')
                    ->descriptionIcon('heroicon-m-chart-bar-square')
                    ->color($color);
            })
            ->all();
    }
}
