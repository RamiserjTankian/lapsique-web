<?php

namespace App\Filament\Resources\ContentBookings\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn ($record) => $record->client_email),

                TextColumn::make('customer.email')
                    ->label('Portal')
                    ->placeholder('Sin portal')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('slot.date')
                    ->label('Sesión')
                    ->date('d/m/Y')
                    ->description(fn ($record) => $record->slot?->time_label ?? '—'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->status_label)
                    ->color(fn ($record) => $record->status_color),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn ($record) => $record->formatted_amount),

                TextColumn::make('deliverables_count')
                    ->label('Archivos')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('utm_source')
                    ->label('Fuente')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending_payment' => 'Pendiente de pago',
                        'confirmed' => 'Confirmada',
                        'pending' => 'Pago en revisión',
                        'failed' => 'Pago fallido',
                        'cancelled' => 'Cancelada',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
