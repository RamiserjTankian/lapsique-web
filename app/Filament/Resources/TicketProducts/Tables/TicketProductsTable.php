<?php

namespace App\Filament\Resources\TicketProducts\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketProductsTable
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
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? '∞'),
                TextColumn::make('reserved_count')
                    ->label('Reservado')
                    ->sortable(),
                TextColumn::make('sold_count')
                    ->label('Vendidos')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label('Inicio venta')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label('Fin venta')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'title')
                    ->label('Evento'),
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'ticket' => 'Ticket',
                        'table' => 'Mesa',
                        'combo' => 'Combo',
                        'multipass' => 'MultiPass',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Activo')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
