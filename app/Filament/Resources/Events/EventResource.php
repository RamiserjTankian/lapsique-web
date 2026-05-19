<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'Eventos';

    protected static UnitEnum|string|null $navigationGroup = 'Eventos';

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
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
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'ticketOrders as paid_ticket_orders_count' => fn (Builder $query): Builder => $query->where('status', 'paid'),
                'ticketAttendees as registered_ticket_accesses' => fn (Builder $query): Builder => $query->whereIn('status', ['registered', 'checked_in']),
            ])
            ->withSum([
                'ticketOrders as paid_ticket_revenue' => fn (Builder $query): Builder => $query->where('status', 'paid'),
            ], 'total')
            ->withSum([
                'ticketOrders as paid_ticket_subtotal' => fn (Builder $query): Builder => $query->where('status', 'paid'),
            ], 'subtotal')
            ->withSum([
                'ticketOrders as paid_ticket_fee' => fn (Builder $query): Builder => $query->where('status', 'paid'),
            ], 'fee')
            ->withSum([
                'ticketOrders as paid_ticket_accesses' => fn (Builder $query): Builder => $query->where('status', 'paid'),
            ], 'attendees_expected');
    }
}
