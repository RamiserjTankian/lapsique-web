<?php

namespace App\Filament\Resources\BookingSlots;

use App\Filament\Resources\BookingSlots\Pages\CreateBookingSlot;
use App\Filament\Resources\BookingSlots\Pages\EditBookingSlot;
use App\Filament\Resources\BookingSlots\Pages\ListBookingSlots;
use App\Filament\Resources\BookingSlots\Schemas\BookingSlotForm;
use App\Filament\Resources\BookingSlots\Tables\BookingSlotsTable;
use App\Models\BookingSlot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BookingSlotResource extends Resource
{
    protected static ?string $model = BookingSlot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Horarios';

    protected static ?string $modelLabel = 'Horario';

    protected static ?string $pluralModelLabel = 'Horarios Disponibles';

    protected static UnitEnum|string|null $navigationGroup = 'Booking';

    protected static ?int $navigationSort = 0;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return BookingSlotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingSlotsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingSlots::route('/'),
            'create' => CreateBookingSlot::route('/create'),
            'edit' => EditBookingSlot::route('/{record}/edit'),
        ];
    }
}
