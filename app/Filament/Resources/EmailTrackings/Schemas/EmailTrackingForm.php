<?php

namespace App\Filament\Resources\EmailTrackings\Schemas;

use App\Models\ContactLog;
use App\Models\Customer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EmailTrackingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('contact_log_id')
                    ->label('Contact Log')
                    ->relationship('contactLog', 'subject')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('subject')
                            ->label('Asunto')
                            ->required(),
                    ])
                    ->columnSpanFull(),
                    
                Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                    ]),
                    
                TextInput::make('tracking_token')
                    ->label('Token de Tracking')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Token único generado automáticamente'),
                    
                Select::make('device_type')
                    ->label('Tipo de Dispositivo')
                    ->options([
                        'desktop' => 'Desktop',
                        'mobile' => 'Móvil',
                        'tablet' => 'Tablet',
                        'unknown' => 'Desconocido',
                    ])
                    ->default('unknown'),
                    
                TextInput::make('opens_count')
                    ->label('Contador de Aperturas')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->disabled()
                    ->helperText('Se actualiza automáticamente'),
                    
                TextInput::make('clicks_count')
                    ->label('Contador de Clics')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->disabled()
                    ->helperText('Se actualiza automáticamente'),
                    
                DateTimePicker::make('first_opened_at')
                    ->label('Primera Apertura')
                    ->timezone('America/Mexico_City')
                    ->disabled()
                    ->helperText('Se registra automáticamente'),
                    
                DateTimePicker::make('last_opened_at')
                    ->label('Última Apertura')
                    ->timezone('America/Mexico_City')
                    ->disabled()
                    ->helperText('Se actualiza automáticamente'),
                    
                DateTimePicker::make('first_clicked_at')
                    ->label('Primer Click')
                    ->timezone('America/Mexico_City')
                    ->disabled()
                    ->helperText('Se registra automáticamente'),
                    
                DateTimePicker::make('last_clicked_at')
                    ->label('Último Click')
                    ->timezone('America/Mexico_City')
                    ->disabled()
                    ->helperText('Se actualiza automáticamente'),
                    
                TextInput::make('ip_address')
                    ->label('Dirección IP')
                    ->maxLength(45)
                    ->disabled()
                    ->helperText('Se registra automáticamente'),
                    
                Textarea::make('user_agent')
                    ->label('User Agent')
                    ->rows(2)
                    ->disabled()
                    ->helperText('Información del navegador/dispositivo'),
                    
                Textarea::make('clicked_links')
                    ->label('Links Clickeados')
                    ->rows(3)
                    ->disabled()
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'Ninguno')
                    ->helperText('Lista de URLs clickeadas (JSON)'),
            ]);
    }
}
