<?php

namespace App\Filament\Resources\GuestListEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class GuestListEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('event.title')
                    ->label('Evento')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('instagram_handle')
                    ->label('Instagram')
                    ->formatStateUsing(fn (?string $state) => $state ? '@' . $state : '—')
                    ->toggleable(),
                TextColumn::make('gender')
                    ->label('Género')
                    ->sortable(),
                IconColumn::make('accepts_emails')
                    ->label('Acepta info')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('created_at')
                    ->label('Registro')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('event')
                    ->relationship('event', 'title')
                    ->label('Evento'),
                TernaryFilter::make('accepts_emails')
                    ->label('Acepta info'),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'rejected' => 'Rechazado',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
