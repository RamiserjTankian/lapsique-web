<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ContentBookingSalesOverviewWidget;
use App\Filament\Widgets\ContentBookingSalesTimelineWidget;
use BackedEnum;
use Filament\Pages\Dashboard;
use UnitEnum;

class ContentBookingSalesDashboard extends Dashboard
{
    protected static string $routePath = 'content-booking-sales';

    protected static ?string $navigationLabel = 'Ventas de sesiones';

    protected static ?string $title = 'Ventas de sesiones de contenido';

    protected static UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 2;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getWidgets(): array
    {
        return [
            ContentBookingSalesOverviewWidget::class,
            ContentBookingSalesTimelineWidget::class,
        ];
    }
}
