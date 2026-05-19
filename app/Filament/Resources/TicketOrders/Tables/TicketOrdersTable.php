<?php

namespace App\Filament\Resources\TicketOrders\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketOrdersTable
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
                TextColumn::make('public_id')
                    ->label('Orden')
                    ->searchable(),
                TextColumn::make('payment_provider')
                    ->label('Proveedor')
                    ->badge(),
                TextColumn::make('buyer_name')
                    ->label('Comprador')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('buyer_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => ['failed', 'cancelled'],
                        'info' => 'refunded',
                    ]),
                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('attendees_registered')
                    ->label('Registrados')
                    ->sortable(),
                TextColumn::make('attendees_expected')
                    ->label('Esperados')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'title')
                    ->label('Evento'),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'paid' => 'Pagado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                        'refunded' => 'Reembolsado',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
