<?php

namespace App\Filament\Resources\BookingSlots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingSlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('time_label')
                    ->label('Horario')
                    ->searchable(),

                TextColumn::make('booked_count')
                    ->label('Reservas')
                    ->formatStateUsing(fn ($record) => $record->booked_count . ' / ' . $record->max_bookings)
                    ->badge()
                    ->color(fn ($record) => $record->booked_count >= $record->max_bookings ? 'danger' : 'success'),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Estado'),

                Filter::make('upcoming')
                    ->label('Solo próximos')
                    ->query(fn (Builder $query) => $query->where('date', '>=', today()))
                    ->default(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'asc');
    }
}
