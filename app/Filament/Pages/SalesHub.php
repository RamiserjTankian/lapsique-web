<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SalesAnalytics\SalesAnalyticsResource;
use App\Filament\Resources\SalesAnalytics\Widgets\SalesOverviewWidget;
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

    public string $activeTab = 'events';

    public function mount(): void
    {
        $this->activeTab = 'events';
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = 'events';
        $this->dispatch('$refresh');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewAllEvents')
                ->label('Ver detalle por evento')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(SalesAnalyticsResource::getUrl('index')),
        ];
    }

    public function getWidgets(): array
    {
        return [
            SalesOverviewWidget::class,
        ];
    }
}
