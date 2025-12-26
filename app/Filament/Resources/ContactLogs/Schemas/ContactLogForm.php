<?php

namespace App\Filament\Resources\ContactLogs\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                        TextInput::make('customer_email')
                            ->label('Email')
                            ->state(fn ($record) => $record?->customer?->email)
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'title')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('channel')
                            ->label('Canal')
                            ->state(fn ($record) => $record?->channel ? ucfirst($record->channel) : '-')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('type')
                            ->label('Tipo')
                            ->state(fn ($record) => $record?->type ? ucfirst($record->type) : '-')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('status')
                            ->label('Estado')
                            ->state(fn ($record) => $record?->status ? ucfirst($record->status) : '-')
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
                Section::make('Estado de Entrega')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sent_at_display')
                            ->label('Enviado')
                            ->state(fn ($record) => $record?->sent_at?->format('d/m/Y H:i') ?? '-')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('delivered_at_display')
                            ->label('Entregado')
                            ->state(fn ($record) => $record?->delivered_at?->format('d/m/Y H:i') ?? '-')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('opened_at_display')
                            ->label('Abierto')
                            ->state(fn ($record) => $record?->opened_at?->format('d/m/Y H:i') ?? '-')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('clicked_at_display')
                            ->label('Clic')
                            ->state(fn ($record) => $record?->clicked_at?->format('d/m/Y H:i') ?? '-')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('failed_at_display')
                            ->label('Fallido')
                            ->state(fn ($record) => $record?->failed_at?->format('d/m/Y H:i') ?? '-')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('created_at_display')
                            ->label('Creado')
                            ->state(fn ($record) => $record?->created_at?->format('d/m/Y H:i') ?? '-')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('error_message')
                            ->label('Error')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        Textarea::make('metadata')
                            ->label('Metadata')
                            ->rows(6)
                            ->state(fn ($record) => $record?->metadata ? json_encode($record->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '-')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
