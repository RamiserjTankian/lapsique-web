<?php

namespace App\Filament\Resources\EmailTrackings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailTrackingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('customer.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                    
                TextColumn::make('contactLog.subject')
                    ->label('Asunto')
                    ->limit(40)
                    ->searchable()
                    ->placeholder('—'),
                    
                TextColumn::make('opens_count')
                    ->label('Aperturas')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'secondary')
                    ->formatStateUsing(fn ($state) => $state ?? 0),
                    
                TextColumn::make('clicks_count')
                    ->label('Clics')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'info' : 'secondary')
                    ->formatStateUsing(fn ($state) => $state ?? 0),
                    
                TextColumn::make('engagement_rate')
                    ->label('Engagement')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . '%' : '0%')
                    ->badge()
                    ->color(fn ($state) => $state >= 50 ? 'success' : ($state >= 25 ? 'warning' : 'secondary'))
                    ->sortable(),
                    
                BadgeColumn::make('device_type')
                    ->label('Dispositivo')
                    ->colors([
                        'primary' => 'desktop',
                        'info' => 'mobile',
                        'warning' => 'tablet',
                        'secondary' => 'unknown',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'desktop' => 'Desktop',
                        'mobile' => 'Móvil',
                        'tablet' => 'Tablet',
                        default => 'Desconocido',
                    })
                    ->sortable(),
                    
                TextColumn::make('first_opened_at')
                    ->label('Primera Apertura')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                    
                TextColumn::make('last_clicked_at')
                    ->label('Último Click')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                    
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('device_type')
                    ->label('Dispositivo')
                    ->options([
                        'desktop' => 'Desktop',
                        'mobile' => 'Móvil',
                        'tablet' => 'Tablet',
                        'unknown' => 'Desconocido',
                    ]),
                    
                SelectFilter::make('has_opens')
                    ->label('Aperturas')
                    ->options([
                        '1' => 'Con aperturas',
                        '0' => 'Sin aperturas',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === '1') {
                            return $query->where('opens_count', '>', 0);
                        }
                        if ($state['value'] === '0') {
                            return $query->where('opens_count', 0);
                        }
                        return $query;
                    }),
                    
                SelectFilter::make('has_clicks')
                    ->label('Clics')
                    ->options([
                        '1' => 'Con clics',
                        '0' => 'Sin clics',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === '1') {
                            return $query->where('clicks_count', '>', 0);
                        }
                        if ($state['value'] === '0') {
                            return $query->where('clicks_count', 0);
                        }
                        return $query;
                    }),
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
