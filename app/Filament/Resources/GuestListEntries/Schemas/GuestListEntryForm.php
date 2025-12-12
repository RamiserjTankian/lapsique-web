<?php

namespace App\Filament\Resources\GuestListEntries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GuestListEntryForm
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
                    ->columnSpan(2),
                TextInput::make('full_name')
                    ->label('Nombre completo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel()
                    ->maxLength(30),
                TextInput::make('instagram_handle')
                    ->label('Instagram')
                    ->prefix('@')
                    ->maxLength(255),
                Select::make('gender')
                    ->label('Género')
                    ->options([
                        'femenino' => 'Femenino',
                        'masculino' => 'Masculino',
                        'otro' => 'Otro',
                    ]),
                Toggle::make('accepts_emails')
                    ->label('Acepta envíos de info')
                    ->required(),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'rejected' => 'Rechazado',
                    ])
                    ->default('pending'),
                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
