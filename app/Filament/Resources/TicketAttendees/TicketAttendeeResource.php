<?php

namespace App\Filament\Resources\TicketAttendees;

use App\Filament\Resources\TicketAttendees\Pages\EditTicketAttendee;
use App\Filament\Resources\TicketAttendees\Pages\ListTicketAttendees;
use App\Filament\Resources\TicketAttendees\Schemas\TicketAttendeeForm;
use App\Filament\Resources\TicketAttendees\Tables\TicketAttendeesTable;
use App\Models\TicketAttendee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TicketAttendeeResource extends Resource
{
    protected static ?string $model = TicketAttendee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'Accesos';

    protected static UnitEnum|string|null $navigationGroup = 'Eventos';

    protected static ?string $navigationParentItem = 'Tickets';

    protected static ?int $navigationSort = 22;

    public static function form(Schema $schema): Schema
    {
        return TicketAttendeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketAttendeesTable::configure($table);
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
            'index' => ListTicketAttendees::route('/'),
            'edit' => EditTicketAttendee::route('/{record}/edit'),
        ];
    }
}
