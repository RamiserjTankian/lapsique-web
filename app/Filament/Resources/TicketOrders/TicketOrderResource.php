<?php

namespace App\Filament\Resources\TicketOrders;

use App\Filament\Resources\TicketOrders\Pages\ListTicketOrders;
use App\Filament\Resources\TicketOrders\Pages\ViewTicketOrder;
use App\Filament\Resources\TicketOrders\Schemas\TicketOrderForm;
use App\Filament\Resources\TicketOrders\Tables\TicketOrdersTable;
use App\Models\TicketOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TicketOrderResource extends Resource
{
    protected static ?string $model = TicketOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Órdenes';

    protected static UnitEnum|string|null $navigationGroup = 'Eventos';

    protected static ?string $navigationParentItem = 'Tickets';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return TicketOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketOrdersTable::configure($table);
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
            'index' => ListTicketOrders::route('/'),
            'view' => ViewTicketOrder::route('/{record}'),
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
}
