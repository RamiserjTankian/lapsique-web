<?php

namespace App\Filament\Resources\Djs\Tables;

use App\Models\Dj;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
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
                ImageColumn::make('preview')
                    ->label('Foto')
                    ->getStateUsing(fn (Dj $record): ?string => self::previewUrl($record))
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
                TextColumn::make('booking_status')
                    ->label('Booking')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('nationality')
                    ->label('Nacionalidad')
                    ->toggleable(),
                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                IconColumn::make('trascendental_roster')
                    ->label('TDL roster')
                    ->boolean()
                    ->toggleable(),
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
                SelectFilter::make('trascendental_roster')
                    ->options([
                        1 => 'Roster alterno',
                        0 => 'Fuera del roster',
                    ])
                    ->label('Roster alterno'),
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

    private static function previewUrl(Dj $dj): ?string
    {
        if (filled($dj->public_image_path)) {
            return self::publicAssetUrl($dj->public_image_path);
        }

        return $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile') ?: null;
    }

    private static function publicAssetUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
