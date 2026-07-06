<?php

namespace App\Filament\Resources\Customers\Schemas;

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
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('instagram_handle')
                    ->label('Instagram')
                    ->prefix('@')
                    ->maxLength(255),
                Select::make('status')
                    ->label('Status comercial')
                    ->options([
                        'lead' => 'Lead',
                        'prospect' => 'Prospecto',
                        'customer' => 'Cliente',
                        'inactive' => 'Inactivo',
                    ])
                    ->default('lead')
                    ->required(),
                Select::make('lifecycle_stage')
                    ->label('Etapa')
                    ->options([
                        'subscriber' => 'Subscriber',
                        'lead' => 'Lead',
                        'mql' => 'MQL',
                        'sql' => 'SQL',
                        'customer' => 'Cliente',
                        'evangelist' => 'Evangelist',
                    ])
                    ->default('lead')
                    ->required(),
                TextInput::make('lead_score')
                    ->label('Lead score')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Select::make('source')
                    ->label('Origen')
                    ->options([
                        'popup' => 'Popup del sitio',
                        'guestlist' => 'Guest List',
                        'manual' => 'Manual',
                        'event' => 'Evento',
                        'booking' => 'Reserva',
                        'food_reels' => 'Landing comida',
                        'dj_set' => 'Landing DJ Sets',
                        'drone_session' => 'Landing dron',
                        'construction_progress' => 'Landing avances',
                        'other' => 'Otro',
                    ])
                    ->default('manual'),
                Toggle::make('subscribed_newsletter')
                    ->label('Suscrito al newsletter')
                    ->default(true),
                Toggle::make('subscribed_whatsapp')
                    ->label('Acepta WhatsApp')
                    ->default(false),
                Toggle::make('subscribed_sms')
                    ->label('Acepta SMS')
                    ->default(false),
                TextInput::make('utm_source')
                    ->label('UTM source')
                    ->maxLength(255),
                TextInput::make('utm_campaign')
                    ->label('UTM campaign')
                    ->maxLength(255),
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
