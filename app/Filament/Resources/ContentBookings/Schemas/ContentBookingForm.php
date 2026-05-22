<?php

namespace App\Filament\Resources\ContentBookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContentBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Cliente')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Cliente del portal')
                            ->relationship('customer', 'email')
                            ->searchable()
                            ->preload(),
                        TextInput::make('client_name')
                            ->label('Nombre')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('client_email')
                            ->label('Email')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('client_phone')
                            ->label('WhatsApp')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('client_instagram')
                            ->label('Instagram')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->label('Brief del cliente')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),

                Section::make('Gestión de la sesión')
                    ->schema([
                        Select::make('service_type')
                            ->label('Servicio')
                            ->options([
                                'content_session' => 'Sesión de contenido',
                                'dj_set' => 'DJ Set',
                            ])
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('booking_slot_id')
                            ->label('Horario')
                            ->relationship('slot', 'time_label')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->date->format('d/m/Y').' · '.$record->time_label)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending_payment' => 'Pendiente de pago',
                                'confirmed' => 'Confirmada',
                                'pending' => 'Pago en revisión',
                                'failed' => 'Pago fallido',
                                'cancelled' => 'Cancelada',
                            ])
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Para confirmar o cancelar, usa los botones en la vista de la reserva (no en Editar).'),
                        TextInput::make('shoot_location')
                            ->label('Locación / set'),
                        DateTimePicker::make('deliverables_ready_at')
                            ->label('Entregables publicados')
                            ->seconds(false)
                            ->native(false),
                        Textarea::make('admin_notes')
                            ->label('Notas internas')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),

                Section::make('Entregables del cliente')
                    ->description('Gestiona los enlaces desde la vista de la reserva con el botón «Añadir entregables» o la tabla de enlaces.')
                    ->schema([
                        TextInput::make('deliverables_drive_url')
                            ->label('Último enlace (legacy)')
                            ->url()
                            ->maxLength(2048)
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Se actualiza al añadir enlaces')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }
}
