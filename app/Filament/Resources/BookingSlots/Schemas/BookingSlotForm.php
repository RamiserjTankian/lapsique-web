<?php

namespace App\Filament\Resources\BookingSlots\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BookingSlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('date')
                ->label('Fecha')
                ->required()
                ->minDate(today())
                ->displayFormat('d/m/Y')
                ->native(false),

            TextInput::make('time_label')
                ->label('Etiqueta del horario')
                ->placeholder('Ej: 10:00 AM')
                ->required()
                ->maxLength(20)
                ->helperText('Texto que verá el cliente al elegir el horario.'),

            TimePicker::make('time_value')
                ->label('Hora')
                ->required()
                ->seconds(false)
                ->native(false)
                ->helperText('Hora en formato 24h para ordenar correctamente.'),

            TextInput::make('max_bookings')
                ->label('Máximo de reservas')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1)
                ->maxValue(10),

            Toggle::make('is_active')
                ->label('Disponible')
                ->default(true)
                ->helperText('Desactiva para ocultar este horario sin eliminarlo.'),

            Textarea::make('notes')
                ->label('Notas internas')
                ->nullable()
                ->rows(2)
                ->helperText('Solo visible para el equipo, no para los clientes.'),
        ]);
    }
}
