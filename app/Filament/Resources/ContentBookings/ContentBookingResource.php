<?php

namespace App\Filament\Resources\ContentBookings;

use App\Filament\Resources\ContentBookings\Pages\ListContentBookings;
use App\Filament\Resources\ContentBookings\Pages\EditContentBooking;
use App\Filament\Resources\ContentBookings\Pages\ViewContentBooking;
use App\Filament\Resources\ContentBookings\Schemas\ContentBookingForm;
use App\Filament\Resources\ContentBookings\Schemas\ContentBookingInfolist;
use App\Filament\Resources\ContentBookings\Tables\ContentBookingsTable;
use App\Models\ContentBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContentBookingResource extends Resource
{
    protected static ?string $model = ContentBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Reservas';

    protected static ?string $modelLabel = 'Reserva';

    protected static ?string $pluralModelLabel = 'Reservas de Contenido';

    protected static UnitEnum|string|null $navigationGroup = 'Booking';

    protected static ?int $navigationSort = 1;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return ContentBookingForm::configure($schema);
    }

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return ContentBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentBookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentBookings::route('/'),
            'view' => ViewContentBooking::route('/{record}'),
            'edit' => EditContentBooking::route('/{record}/edit'),
        ];
    }
}
