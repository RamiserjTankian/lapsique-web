<?php

namespace App\Filament\Resources\TicketProducts;

use App\Filament\Resources\TicketProducts\Pages\CreateTicketProduct;
use App\Filament\Resources\TicketProducts\Pages\EditTicketProduct;
use App\Filament\Resources\TicketProducts\Pages\ListTicketProducts;
use App\Filament\Resources\TicketProducts\Schemas\TicketProductForm;
use App\Filament\Resources\TicketProducts\Tables\TicketProductsTable;
use App\Models\TicketProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TicketProductResource extends Resource
{
    protected static ?string $model = TicketProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Tickets';

    protected static UnitEnum|string|null $navigationGroup = 'Eventos';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return TicketProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketProductsTable::configure($table);
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
            'index' => ListTicketProducts::route('/'),
            'create' => CreateTicketProduct::route('/create'),
            'edit' => EditTicketProduct::route('/{record}/edit'),
        ];
    }
}
