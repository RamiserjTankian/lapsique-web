<?php

namespace App\Filament\Resources\ContactLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle del Contacto')
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Invitado')
                            ->relationship('customer', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'title')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('channel')
                            ->label('Canal')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('type')
                            ->label('Tipo')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('status')
                            ->label('Estado')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('Contenido')
                    ->columns(1)
                    ->schema([
                        TextInput::make('subject')
                            ->label('Asunto')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('message')
                            ->label('Mensaje')
                            ->rows(4)
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('Fechas')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sent_at')
                            ->label('Enviado')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('delivered_at')
                            ->label('Entregado')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('opened_at')
                            ->label('Abierto')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('clicked_at')
                            ->label('Clic')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('failed_at')
                            ->label('Fallido')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('created_at')
                            ->label('Creado')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('error_message')
                            ->label('Mensaje de Error')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
