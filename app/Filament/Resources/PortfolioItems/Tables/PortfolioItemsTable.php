<?php

namespace App\Filament\Resources\PortfolioItems\Tables;

use App\Models\PortfolioItem;
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

class PortfolioItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('priority')
            ->columns([
                ImageColumn::make('preview')
                    ->label('Preview')
                    ->getStateUsing(fn (PortfolioItem $record): ?string => self::previewUrl($record))
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

    private static function previewUrl(PortfolioItem $item): ?string
    {
        if (filled($item->poster_path)) {
            return self::publicAssetUrl($item->poster_path);
        }

        $posterUrl = $item->getFirstMediaUrl('poster', 'thumb') ?: $item->getFirstMediaUrl('poster');

        if (filled($posterUrl)) {
            return $posterUrl;
        }

        if ($item->source === 'youtube' && filled($item->youtube_id)) {
            return "https://img.youtube.com/vi/{$item->youtube_id}/maxresdefault.jpg";
        }

        if (filled($item->asset_path) && self::isImagePath($item->asset_path)) {
            return self::publicAssetUrl($item->asset_path);
        }

        $asset = $item->getFirstMedia('asset');

        if ($asset && str_starts_with((string) $asset->mime_type, 'image/')) {
            return $item->getFirstMediaUrl('asset', 'thumb') ?: $item->getFirstMediaUrl('asset');
        }

        return null;
    }

    private static function publicAssetUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    private static function isImagePath(string $path): bool
    {
        return (bool) preg_match('/\.(avif|gif|jpe?g|png|webp)(\?.*)?$/i', $path);
    }
}
