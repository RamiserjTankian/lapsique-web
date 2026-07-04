<?php

namespace App\Filament\Resources\ContentBookings\Schemas;

use App\Models\BookingSlot;
use App\Models\ContentBooking;
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
                            ->required()
                            ->maxLength(255),
                        TextInput::make('client_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('client_phone')
                            ->label('WhatsApp')
                            ->tel()
                            ->required()
                            ->maxLength(30),
                        TextInput::make('client_instagram')
                            ->label('Instagram')
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label('Brief del cliente')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),

                Section::make('Gestión de la sesión')
                    ->schema([
                        Select::make('service_type')
                            ->label('Servicio')
                            ->options(ContentBooking::serviceOptions())
                            ->default(ContentBooking::SERVICE_CONTENT_SESSION)
                            ->required()
                            ->live(),
                        Select::make('booking_slot_id')
                            ->label('Horario')
                            ->options(function (?ContentBooking $record = null): array {
                                return BookingSlot::query()
                                    ->where(function ($query) use ($record): void {
                                        $query->available();

                                        if ($record?->booking_slot_id) {
                                            $query->orWhereKey($record->booking_slot_id);
                                        }
                                    })
                                    ->orderBy('date')
                                    ->orderBy('time_value')
                                    ->get()
                                    ->mapWithKeys(fn (BookingSlot $slot): array => [
                                        $slot->id => $slot->date->format('d/m/Y').' · '.$slot->time_label.' · '.$slot->remaining.' cupo(s)',
                                    ])
                                    ->all();
                            })
                            ->searchable()
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
                            ->default('pending_payment')
                            ->required()
                            ->helperText('Para enviar confirmación, usa el botón en la vista de la reserva.'),
                        Select::make('payment_provider')
                            ->label('Proveedor de pago')
                            ->options([
                                'internal' => 'Manual / interno',
                                'stripe' => 'Stripe',
                                'mercadopago' => 'Mercado Pago',
                            ])
                            ->default('internal')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Monto')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('MXN')
                            ->helperText('Si lo dejas vacío al crear, se usa el precio del servicio.'),
                        TextInput::make('currency')
                            ->label('Moneda')
                            ->default('MXN')
                            ->maxLength(3),
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
