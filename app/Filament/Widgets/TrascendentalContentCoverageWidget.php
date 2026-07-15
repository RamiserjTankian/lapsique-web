<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Djs\DjResource;
use App\Filament\Resources\Events\EventResource;
use App\Models\Customer;
use App\Models\Dj;
use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrascendentalContentCoverageWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Eventos públicos', Event::query()->where('trascendental_visible', true)->count())
                ->description('Home, Eventos y proyectos producidos')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->url(EventResource::getUrl())
                ->color('primary'),
            Stat::make('Artistas del roster', Dj::query()->where('trascendental_roster', true)->count())
                ->description('Home y Tours & Routing')
                ->descriptionIcon('heroicon-m-musical-note')
                ->url(DjResource::getUrl())
                ->color('success'),
            Stat::make('Casos publicados', Event::query()->where('is_case_study', true)->count())
                ->description('Home y Casos')
                ->descriptionIcon('heroicon-m-briefcase')
                ->url(EventResource::getUrl())
                ->color('warning'),
            Stat::make('Leads Trascendental', Customer::query()
                ->whereIn('source', ['trascendental_join_list', 'trascendental_contact'])
                ->count())
                ->description('Join The List y formulario de contacto')
                ->descriptionIcon('heroicon-m-user-plus')
                ->url(CustomerResource::getUrl())
                ->color('info'),
        ];
    }
}
