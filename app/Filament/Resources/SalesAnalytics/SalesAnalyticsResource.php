<?php

namespace App\Filament\Resources\SalesAnalytics;

use App\Filament\Resources\SalesAnalytics\Pages\ListSalesAnalytics;
use App\Filament\Resources\SalesAnalytics\Pages\ViewSalesAnalytics;
use App\Filament\Resources\SalesAnalytics\Tables\SalesAnalyticsTable;
use App\Models\TicketOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class SalesAnalyticsResource extends Resource
{
    protected static ?string $model = TicketOrder::class;

    protected static ?string $recordRouteKeyName = 'id';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Detalle por evento';

    protected static UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return SalesAnalyticsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesAnalytics::route('/'),
            'view' => ViewSalesAnalytics::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $aggregateQuery = TicketOrder::query()
            ->select([
                DB::raw('MIN(ticket_orders.id) as id'),
                DB::raw('MIN(ticket_orders.id) as public_id'),
                'ticket_orders.event_id',
                DB::raw('MIN(ticket_orders.currency) as currency'),
                DB::raw('MIN(ticket_orders.payment_provider) as payment_provider'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(ticket_orders.attendees_expected) as tickets_sold'),
                DB::raw('SUM(ticket_orders.attendees_registered) as tickets_registered'),
                DB::raw('SUM(ticket_orders.subtotal) as revenue_subtotal'),
                DB::raw('SUM(ticket_orders.fee) as revenue_fee'),
                DB::raw('SUM(ticket_orders.total) as revenue_total'),
                DB::raw('MIN(ticket_orders.paid_at) as first_paid_at'),
                DB::raw('MAX(ticket_orders.paid_at) as last_paid_at'),
            ])
            ->where('ticket_orders.status', 'paid')
            ->groupBy('ticket_orders.event_id');

        return TicketOrder::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->fromSub($aggregateQuery, 'ticket_orders')
            ->with('event');
    }
}
