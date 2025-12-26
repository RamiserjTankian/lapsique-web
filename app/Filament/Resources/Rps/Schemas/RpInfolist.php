<?php

namespace App\Filament\Resources\Rps\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RpInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Información Personal')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->copyable(),
                        TextEntry::make('whatsapp')
                            ->label('WhatsApp')
                            ->copyable(),
                        TextEntry::make('instagram_handle')
                            ->label('Instagram')
                            ->formatStateUsing(fn ($state) => $state ? '@' . $state : '-'),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn ($state) => $state === 'active' ? 'success' : 'danger'),
                        TextEntry::make('commission_rate')
                            ->label('Tasa de Comisión')
                            ->formatStateUsing(fn ($state) => $state ? $state . '%' : '-'),
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Estadísticas Generales')
                    ->schema([
                        TextEntry::make('djs_count')
                            ->label('DJs Asociados')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('customers_count')
                            ->label('Clientes Asociados')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('events_count')
                            ->label('Eventos')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('guestListEntries_count')
                            ->label('Total Guest List')
                            ->badge()
                            ->color('warning'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}
