<?php

namespace App\Filament\Resources\BookingLandingAnalytics;

use App\Filament\Resources\BookingLandingAnalytics\Pages\ListBookingLandingAnalytics;
use App\Filament\Resources\BookingLandingAnalytics\Tables\BookingLandingAnalyticsTable;
use App\Models\AnalyticsSession;
use App\Services\BookingLandingAnalyticsService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class BookingLandingAnalyticsResource extends Resource
{
    protected static ?string $model = AnalyticsSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Embudo Sesión de Contenido';

    protected static ?string $modelLabel = 'Sesión analítica';

    protected static ?string $pluralModelLabel = 'Embudo Sesión de Contenido';

    protected static UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return BookingLandingAnalyticsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingLandingAnalytics::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(BookingLandingAnalyticsService::class)
            ->baseQuery()
            ->latest('created_at');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
}
