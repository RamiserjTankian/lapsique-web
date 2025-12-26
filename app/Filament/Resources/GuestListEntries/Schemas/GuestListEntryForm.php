<?php

namespace App\Filament\Resources\GuestListEntries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                    ->required()
                    ->reactive()
                    ->columnSpan(2),
                Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name', fn (Builder $query) => $query->orderBy('name'))
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel(),
                    ])
                    ->columnSpan(2),
                Select::make('dj_id')
                    ->label('DJ')
                    ->relationship('dj', 'name', function (Builder $query, callable $get) {
                        $eventId = $get('event_id');
                        if ($eventId) {
                            return $query->whereHas('events', fn ($q) => $q->where('events.id', $eventId));
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->helperText('DJ que invitó a este cliente'),
                Select::make('rp_id')
                    ->label('RP')
                    ->relationship('rp', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('RP que gestionó esta invitación'),
                Select::make('gender')
                    ->label('Género')
                    ->options([
                        'femenino' => 'Femenino',
                        'masculino' => 'Masculino',
                        'otro' => 'Otro',
                    ]),
                TextInput::make('plus_ones')
                    ->label('Acompañantes')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('check_in_limit')
                    ->label('Usos QR')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->helperText('Cantidad de accesos permitidos con este QR.'),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'attended' => 'Asistió',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'No asistió',
                    ])
                    ->default('pending')
                    ->required(),
                TextInput::make('qr_quantity')
                    ->label('Cantidad de QRs')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->helperText('Crea múltiples registros con QRs únicos para el mismo correo.')
                    ->dehydrated(false)
                    ->visible(fn (?string $operation = null): bool => $operation === 'create'),
                TextInput::make('invited_by')
                    ->label('Invitado por')
                    ->maxLength(255)
                    ->helperText('Referencia adicional (opcional)'),
                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
