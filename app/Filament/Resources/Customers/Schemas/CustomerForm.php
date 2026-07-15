<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre completo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('instagram_handle')
                    ->label('Instagram')
                    ->prefix('@')
                    ->maxLength(255),
                Select::make('source')
                    ->label('Origen')
                    ->options(fn (?Customer $record): array => Customer::sourceOptions($record?->source))
                    ->searchable()
                    ->default('manual'),
                Toggle::make('subscribed_newsletter')
                    ->label('Suscrito al newsletter')
                    ->default(true),
                DateTimePicker::make('last_interaction_at')
                    ->label('Última interacción')
                    ->timezone('America/Mexico_City')
                    ->disabled(),
                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
