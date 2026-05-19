<?php

namespace App\Filament\Resources\Automations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;

class AutomationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                    
                BadgeColumn::make('trigger_type')
                    ->label('Trigger')
                    ->colors([
                        'success' => ['signup', 'event_registration'],
                        'warning' => ['event_reminder', 'birthday', 'anniversary'],
                        'info' => ['tag_added', 'lifecycle_change', 'score_threshold', 'email_opened'],
                        'secondary' => 'abandoned_cart',
                    ]),
                    
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'paused',
                        'secondary' => 'archived',
                    ]),
                    
                TextColumn::make('total_triggered')
                    ->label('Triggered')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('total_completed')
                    ->label('Completed')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                    
                TextColumn::make('total_failed')
                    ->label('Failed')
                    ->sortable()
                    ->badge()
                    ->color('danger')
                    ->toggleable(),
                    
                TextColumn::make('completion_rate')
                    ->label('Success %')
                    ->formatStateUsing(fn ($record) => $record->completion_rate . '%')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('trigger_type')
                    ->label('Trigger Type')
                    ->options([
                        'signup' => 'Signup',
                        'event_registration' => 'Event Registration',
                        'event_reminder' => 'Event Reminder',
                        'abandoned_cart' => 'Abandoned Cart',
                        'birthday' => 'Birthday',
                        'anniversary' => 'Anniversary',
                        'tag_added' => 'Tag Added',
                        'lifecycle_change' => 'Lifecycle Change',
                        'score_threshold' => 'Score Threshold',
                        'email_opened' => 'Email Opened',
                    ]),
                    
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'archived' => 'Archived',
                    ]),
                    
                TrashedFilter::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
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
