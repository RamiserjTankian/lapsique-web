<?php

namespace App\Filament\Resources\Djs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DjsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('priority')
            ->columns([
                SpatieMediaLibraryImageColumn::make('profile')
                    ->label('Foto')
                    ->collection('profile')
                    ->square()
                    ->size(56),
                TextColumn::make('name')
                    ->label('DJ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('instagram_handle')
                    ->label('Instagram')
                    ->formatStateUsing(fn (?string $state) => $state ? '@' . $state : '—')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                TextColumn::make('priority')
                    ->label('Orden')
                    ->sortable(),
                TextColumn::make('guestListEntries_count')
                    ->label('Guest List')
                    ->counts('guestListEntries')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Alta')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('is_featured')
                    ->options([
                        1 => 'Solo destacados',
                        0 => 'No destacados',
                    ])
                    ->label('Destacado'),
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
