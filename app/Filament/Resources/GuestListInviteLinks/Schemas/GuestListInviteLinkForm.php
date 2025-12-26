<?php

namespace App\Filament\Resources\GuestListInviteLinks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class GuestListInviteLinkForm
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
                    ->helperText('Opcional: Link específico para un DJ'),
                Select::make('rp_id')
                    ->label('RP')
                    ->relationship('rp', 'name', function (Builder $query, callable $get) {
                        $eventId = $get('event_id');
                        if ($eventId) {
                            return $query->whereHas('events', fn ($q) => $q->where('events.id', $eventId));
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->helperText('Opcional: Link específico para un RP'),
                TextInput::make('name')
                    ->label('Nombre del Link')
                    ->maxLength(255)
                    ->helperText('Nombre descriptivo para identificar el link'),
                TextInput::make('token')
                    ->label('Token')
                    ->default(fn () => \App\Models\GuestListInviteLink::generateToken())
                    ->required()
                    ->maxLength(64)
                    ->unique(ignoreRecord: true)
                    ->helperText('Token único para el link'),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
                TextInput::make('max_registrations')
                    ->label('Límite de Registros')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Dejar vacío para sin límite'),
                DateTimePicker::make('expires_at')
                    ->label('Fecha de Expiración')
                    ->timezone('America/Mexico_City')
                    ->helperText('Opcional: Fecha en que expira el link'),
            ]);
    }
}
