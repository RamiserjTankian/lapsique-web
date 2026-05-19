<?php

namespace App\Filament\Resources\BookingAvailabilityRules;

use App\Filament\Resources\BookingAvailabilityRules\Pages\CreateBookingAvailabilityRule;
use App\Filament\Resources\BookingAvailabilityRules\Pages\EditBookingAvailabilityRule;
use App\Filament\Resources\BookingAvailabilityRules\Pages\ListBookingAvailabilityRules;
use App\Filament\Resources\BookingAvailabilityRules\Schemas\BookingAvailabilityRuleForm;
use App\Filament\Resources\BookingAvailabilityRules\Tables\BookingAvailabilityRulesTable;
use App\Models\BookingAvailabilityRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BookingAvailabilityRuleResource extends Resource
{
    protected static ?string $model = BookingAvailabilityRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Disponibilidad';

    protected static ?string $modelLabel = 'Regla de disponibilidad';

    protected static ?string $pluralModelLabel = 'Disponibilidad Semanal';

    protected static UnitEnum|string|null $navigationGroup = 'Booking';

    protected static ?int $navigationSort = 2;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return BookingAvailabilityRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingAvailabilityRulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingAvailabilityRules::route('/'),
            'create' => CreateBookingAvailabilityRule::route('/create'),
            'edit' => EditBookingAvailabilityRule::route('/{record}/edit'),
        ];
    }
}
