<?php

namespace App\Filament\Resources\BookingAvailabilityRules\Tables;

use App\Models\BookingAvailabilityRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingAvailabilityRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Día')
                    ->formatStateUsing(fn ($state) => BookingAvailabilityRule::$dayNames[$state] ?? "Día $state")
                    ->sortable(),

                TextColumn::make('time_label')
                    ->label('Horario')
                    ->sortable(),

                TextColumn::make('max_bookings')
                    ->label('Cupos')
                    ->badge(),

                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->defaultSort('day_of_week')
            ->reorderable('day_of_week')
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
