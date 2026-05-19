<?php

namespace App\Filament\Resources\PortfolioItems\Tables;

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

class PortfolioItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('priority')
            ->columns([
                SpatieMediaLibraryImageColumn::make('asset')
                    ->label('Preview')
                    ->collection('asset')
                    ->square()
                    ->size(64),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->default('—'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'video' ? 'Reel' : 'Foto'),
                TextColumn::make('source')
                    ->label('Fuente')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'youtube' ? 'YouTube' : 'Archivo'),
                TextColumn::make('orientation')
                    ->label('Orientación')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'vertical' ? 'Vertical' : 'Horizontal'),
                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('priority')
                    ->label('Orden')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'photo' => 'Foto',
                        'video' => 'Reel',
                    ]),
                SelectFilter::make('orientation')
                    ->label('Orientación')
                    ->options([
                        'horizontal' => 'Horizontal',
                        'vertical' => 'Vertical',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Visible')
                    ->options([
                        1 => 'Sí',
                        0 => 'No',
                    ]),
            ])
            ->recordActions([
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
