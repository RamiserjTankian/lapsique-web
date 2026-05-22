<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Filament\Resources\SalesAnalytics\SalesAnalyticsResource;
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

    public string $activeTab = 'sessions';

    public function mount(): void
    {
        $tab = (string) request()->query('tab', 'sessions');

        if (in_array($tab, ['events', 'sessions'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function setActiveTab(string $tab): void
    {
        if (! in_array($tab, ['events', 'sessions'], true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->dispatch('$refresh');
    }

    protected function getHeaderActions(): array
    {
        $tabActions = [
            Action::make('tab_sessions')
                ->label('Sesiones')
                ->color($this->activeTab === 'sessions' ? 'primary' : 'gray')
                ->action(fn () => $this->setActiveTab('sessions')),
            Action::make('tab_events')
                ->label('Eventos')
                ->color($this->activeTab === 'events' ? 'primary' : 'gray')
                ->action(fn () => $this->setActiveTab('events')),
        ];

        $detailAction = match ($this->activeTab) {
            'events' => Action::make('viewAllEvents')
                ->label('Ver detalle por evento')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(SalesAnalyticsResource::getUrl('index')),
            default => Action::make('viewAllBookings')
                ->label('Ver todas las reservas')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(ContentBookingResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'confirmed'],
                    ],
                ])),
        };

        return array_merge($tabActions, [$detailAction]);
    }

    public function getWidgets(): array
    {
        return match ($this->activeTab) {
            'events' => [
                SalesOverviewWidget::class,
            ],
            default => [
                ContentBookingSalesOverviewWidget::class,
                ContentBookingSalesTimelineWidget::class,
                ContentBookingSalesOrdersTableWidget::class,
            ],
        };
    }
}
