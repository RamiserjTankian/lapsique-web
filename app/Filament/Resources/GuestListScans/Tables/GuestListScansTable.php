<?php

namespace App\Filament\Resources\GuestListScans\Tables;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GuestListScansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['guestListEntry.event', 'guestListEntry.customer', 'user']))
            ->defaultSort('scanned_at', 'desc')
            ->columns([
                TextColumn::make('guestListEntry.event.title')
                    ->label('Evento')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('guestListEntry.customer.name')
                    ->label('Invitado')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('guestListEntry.customer.email')
                    ->label('Email')
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('scan_status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'checked_in' => 'Escaneado',
                        'duplicate' => 'Reescaneado',
                        default => ucfirst($state ?? ''),
                    })
                    ->colors([
                        'success' => 'checked_in',
                        'warning' => 'duplicate',
                    ]),
                TextColumn::make('scanned_at')
                    ->label('Escaneo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Staff')
                    ->toggleable(),
                TextColumn::make('guestListEntry.check_in_at')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Evento')
                    ->options(Event::orderBy('title', 'asc')->pluck('title', 'id'))
                    ->query(fn ($query, $value) => $query->whereHas('guestListEntry', fn ($subQuery) => $subQuery->where('event_id', $value))),
                SelectFilter::make('scan_status')
                    ->label('Estado')
                    ->options([
                        'checked_in' => 'Escaneado',
                        'duplicate' => 'Reescaneado',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Staff')
                    ->relationship('user', 'name')
                    ->searchable(),
            ])
            ->paginated(true)
            ->defaultPaginationPageOption(25);
    }
}
