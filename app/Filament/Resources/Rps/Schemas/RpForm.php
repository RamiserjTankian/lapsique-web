<?php

namespace App\Filament\Resources\Rps\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RpForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre completo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('instagram_handle')
                    ->label('Instagram')
                    ->prefix('@')
                    ->maxLength(255),
                TextInput::make('commission_rate')
                    ->label('Tasa de comisión (%)')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                ToggleButtons::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                    ])
                    ->default('active')
                    ->required()
                    ->inline()
                    ->columnSpan(2),
                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('djs')
                    ->label('DJs Asociados')
                    ->relationship('djs', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Select::make('customers')
                    ->label('Clientes Asociados')
                    ->relationship('customers', 'name', fn (Builder $query) => $query->orderBy('name'))
                    ->multiple()
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->columnSpanFull(),
                Select::make('events')
                    ->label('Eventos Asociados')
                    ->relationship('events', 'title', fn (Builder $query) => $query->orderBy('starts_at', 'desc'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Eventos en los que este RP está trabajando')
                    ->columnSpanFull(),
            ]);
    }
}
