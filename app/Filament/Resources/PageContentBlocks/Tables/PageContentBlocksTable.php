<?php

namespace App\Filament\Resources\PageContentBlocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PageContentBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('priority')
            ->columns([
                TextColumn::make('page')
                    ->label('Página')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('section')
                    ->label('Sección')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('key')
                    ->label('Clave')
                    ->searchable(),
                TextColumn::make('locale')
                    ->label('Idioma')
                    ->badge(),
                TextColumn::make('title')
                    ->label('Título')
                    ->limit(40)
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('priority')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('site')
                    ->label('Sitio')
                    ->options([
                        'trascendental' => 'Trascendental',
                    ]),
                SelectFilter::make('locale')
                    ->label('Idioma')
                    ->options([
                        'en' => 'English',
                        'es' => 'Español',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Activo')
                    ->options([
                        1 => 'Activo',
                        0 => 'Inactivo',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
