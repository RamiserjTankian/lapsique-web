<?php

namespace App\Filament\Resources\TicketProducts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TicketProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('event_id')
                    ->label('Evento')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(2),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Select::make('category')
                    ->label('Categoría')
                    ->options([
                        'ticket' => 'Ticket',
                        'consumo' => 'Consumo mínimo',
                        'table' => 'Mesa',
                        'combo' => 'Combo',
                        'multipass' => 'MultiPass',
                    ])
                    ->required()
                    ->default('ticket'),
                TextInput::make('price')
                    ->label('Precio total (incluye cargo de servicio)')
                    ->numeric()
                    ->required()
                    ->helperText('Precio final que paga el cliente. Si hay cargo de servicio, ya debe incluirlo.'),
                TextInput::make('service_charge_pct')
                    ->label('Cargo de servicio (%)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Ej: 15 para 15%. Se mostrará el desglose base + cargo al cliente. 0 = sin cargo.'),
                TextInput::make('currency')
                    ->label('Moneda')
                    ->maxLength(3)
                    ->default('MXN')
                    ->required(),
                TextInput::make('access_units')
                    ->label('Accesos por unidad')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->helperText('Cuántas personas deben registrarse por cada unidad.'),
                TextInput::make('check_in_limit')
                    ->label('Usos por QR')
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                TextInput::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Deja vacío para ilimitado.'),
                TextInput::make('max_per_order')
                    ->label('Máximo por orden')
                    ->numeric()
                    ->minValue(1),
                DateTimePicker::make('starts_at')
                    ->label('Inicio de venta'),
                DateTimePicker::make('ends_at')
                    ->label('Fin de venta'),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpan(2),
            ]);
    }
}
