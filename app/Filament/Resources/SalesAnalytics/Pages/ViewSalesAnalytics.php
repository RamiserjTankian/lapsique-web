<?php

namespace App\Filament\Resources\SalesAnalytics\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\SalesAnalytics\SalesAnalyticsResource;
use App\Filament\Resources\SalesAnalytics\Schemas\SalesAnalyticsInfolist;
use App\Filament\Resources\SalesAnalytics\Widgets\EventSalesBreakdownWidget;
use App\Filament\Resources\SalesAnalytics\Widgets\EventSalesOrdersTableWidget;
use App\Filament\Resources\SalesAnalytics\Widgets\EventSalesOverviewWidget;
use App\Filament\Resources\SalesAnalytics\Widgets\EventSalesTimelineChartWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewSalesAnalytics extends ViewRecord
{
    protected static string $resource = SalesAnalyticsResource::class;

    public function infolist(Schema $schema): Schema
    {
        return SalesAnalyticsInfolist::configure($schema);
    }

    public function getTitle(): string
    {
        return $this->getRecord()->event?->title
            ? 'Ventas de ' . $this->getRecord()->event->title
            : 'Detalle de ventas';
    }

    protected function getHeaderActions(): array
    {
        $event = $this->getRecord()->event;

        return [
            Action::make('public_event')
                ->label('Ver landing')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url($event ? route('events.show', $event) : null, shouldOpenInNewTab: true)
                ->visible((bool) $event),
            Action::make('edit_event')
                ->label('Editar evento')
                ->icon('heroicon-o-pencil-square')
                ->url($event ? EventResource::getUrl('edit', ['record' => $event]) : null)
                ->visible((bool) $event),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EventSalesOverviewWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getFooterWidgets(): array
    {
        return [
            EventSalesTimelineChartWidget::class,
            EventSalesBreakdownWidget::class,
            EventSalesOrdersTableWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 2;
    }
}
