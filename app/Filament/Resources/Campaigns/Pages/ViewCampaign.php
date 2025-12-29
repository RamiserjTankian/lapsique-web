<?php

namespace App\Filament\Resources\Campaigns\Pages;

use App\Filament\Resources\Campaigns\CampaignResource;
use App\Filament\Widgets\CampaignStatsWidget;
use App\Filament\Widgets\CampaignOpensChartWidget;
use App\Filament\Widgets\CampaignClicksChartWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCampaign extends ViewRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CampaignStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CampaignOpensChartWidget::class,
            CampaignClicksChartWidget::class,
        ];
    }
}

