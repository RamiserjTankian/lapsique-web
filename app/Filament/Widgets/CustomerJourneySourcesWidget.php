<?php

namespace App\Filament\Widgets;

use App\Services\CustomerJourneyInsightsService;
use Filament\Widgets\Widget;

class CustomerJourneySourcesWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.customer-journey-sources-widget';

    protected function getViewData(): array
    {
        return [
            'sources' => app(CustomerJourneyInsightsService::class)->dashboard()['sources'],
        ];
    }
}
