<?php

namespace App\Filament\Resources\BookingAvailabilityRules\Schemas;

use App\Models\BookingAvailabilityRule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BookingAvailabilityRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('day_of_week')
                ->label('Día de la semana')
                ->options(BookingAvailabilityRule::$dayOptions)
                ->required()
                ->native(false),

            TextInput::make('time_label')
                ->label('Etiqueta del horario')
                ->placeholder('Ej: 10:00 AM')
                ->required()
                ->maxLength(20)
                ->helperText('Texto visible para el cliente al elegir horario.'),

            TimePicker::make('time_value')
                ->label('Hora (24h)')
                ->required()
                ->seconds(false)
                ->native(false)
                ->helperText('Usada para ordenar los horarios correctamente.'),

            TextInput::make('max_bookings')
                ->label('Máximo de reservas simultáneas')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1)
                ->maxValue(10),

            Toggle::make('is_active')
                ->label('Activa')
                ->default(true),
        ]);
    }
}
