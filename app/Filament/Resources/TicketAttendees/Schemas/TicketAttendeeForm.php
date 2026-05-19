<?php

namespace App\Filament\Resources\TicketAttendees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketAttendeeForm
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
                Select::make('ticket_product_id')
                    ->label('Ticket')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'registered' => 'Registrado',
                        'checked_in' => 'Check-in',
                        'cancelled' => 'Cancelado',
                    ])
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre completo')
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->maxLength(30),
                TextInput::make('instagram_handle')
                    ->label('Instagram')
                    ->maxLength(255),
                TextInput::make('check_in_limit')
                    ->label('Usos QR')
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                TextInput::make('check_in_count')
                    ->label('Usos actuales')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ]);
    }
}
