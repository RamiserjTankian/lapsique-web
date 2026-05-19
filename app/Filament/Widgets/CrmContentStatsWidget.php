<?php

namespace App\Filament\Widgets;

use App\Models\Dj;
use App\Models\Location;
use App\Models\Post;
use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmContentStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalDjs = Dj::count();
        $totalVideos = Video::count();
        $totalPosts = Post::count();
        $totalLocations = Location::count();

        return [
            Stat::make('DJs', number_format($totalDjs))
                ->description('Artistas activos')
                ->descriptionIcon('heroicon-m-musical-note')
                ->color('primary'),
            Stat::make('Videos', number_format($totalVideos))
                ->description('Sets publicados')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('info'),
            Stat::make('Posts', number_format($totalPosts))
                ->description('Blog y noticias')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
            Stat::make('Locations', number_format($totalLocations))
                ->description('Venues y spots')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('warning'),
        ];
    }
}
