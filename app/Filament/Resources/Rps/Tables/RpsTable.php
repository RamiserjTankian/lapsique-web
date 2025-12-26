<?php

namespace App\Filament\Resources\Rps\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RpsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->withCount(['djs', 'customers', 'events', 'guestListEntries']);
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('instagram_handle')
                    ->label('Instagram')
                    ->formatStateUsing(fn ($state) => $state ? '@' . $state : '-')
                    ->toggleable(),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ])
                    ->sortable(),
                TextColumn::make('commission_rate')
                    ->label('Comisión')
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : '-')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('djs_count')
                    ->label('DJs')
                    ->counts('djs')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('customers_count')
                    ->label('Clientes')
                    ->counts('customers')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('events_count')
                    ->label('Eventos')
                    ->counts('events')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                TextColumn::make('guestListEntries_count')
                    ->label('Guest List')
                    ->counts('guestListEntries')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('guestListEntries_confirmed_count')
                    ->label('Confirmados')
                    ->getStateUsing(function ($record) {
                        try {
                            return $record->guestListEntries()->where('status', 'confirmed')->count();
                        } catch (\Exception $e) {
                            return 0;
                        }
                    })
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                TextColumn::make('guestListEntries_attended_count')
                    ->label('Asistieron')
                    ->getStateUsing(function ($record) {
                        try {
                            return $record->guestListEntries()->where('status', 'attended')->count();
                        } catch (\Exception $e) {
                            return 0;
                        }
                    })
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                    ]),
                SelectFilter::make('events')
                    ->label('Evento')
                    ->relationship('events', 'title')
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
