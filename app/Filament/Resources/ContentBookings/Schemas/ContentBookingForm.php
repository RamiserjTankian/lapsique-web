<?php

namespace App\Filament\Resources\ContentBookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
                        Select::make('booking_slot_id')
                            ->label('Horario')
                            ->relationship('slot', 'time_label')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->date->format('d/m/Y') . ' · ' . $record->time_label)
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending_payment' => 'Pendiente de pago',
                                'confirmed' => 'Confirmada',
                                'pending' => 'Pago en revisión',
                                'failed' => 'Pago fallido',
                                'cancelled' => 'Cancelada',
                            ])
                            ->required(),
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
                    ->description('Archivos visibles dentro del portal del cliente.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('deliverables')
                            ->label('Material final')
                            ->collection('deliverables')
                            ->multiple()
                            ->openable()
                            ->downloadable()
                            ->reorderable()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/jpg',
                                'video/mp4',
                                'video/quicktime',
                                'video/webm',
                                'application/pdf',
                                'application/zip',
                                'application/x-zip-compressed',
                            ])
                            ->maxSize(512000)
                            ->helperText('Puedes subir fotos, reels, PDFs o ZIPs de entrega.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
