<?php

namespace App\Filament\Resources\GuestListInviteLinks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GuestListInviteLinkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Información del Link')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('event.title')
                            ->label('Evento'),
                        TextEntry::make('dj.name')
                            ->label('DJ')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('rp.name')
                            ->label('RP')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('token')
                            ->label('Token')
                            ->copyable(),
                        TextEntry::make('invite_url')
                            ->label('URL del Link')
                            ->copyable()
                            ->url(fn ($record) => $record->invite_url)
                            ->openUrlInNewTab(),
                        TextEntry::make('is_active')
                            ->label('Activo')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
                        TextEntry::make('current_registrations')
                            ->label('Registros Actuales')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('max_registrations')
                            ->label('Límite de Registros')
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('expires_at')
                            ->label('Fecha de Expiración')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
