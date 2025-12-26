<?php

namespace App\Filament\Resources\Campaigns\Tables;

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

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                    
                BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'email',
                        'success' => 'sms',
                        'info' => 'whatsapp',
                        'warning' => 'multi_channel',
                    ]),
                    
                BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'scheduled',
                        'success' => 'active',
                        'danger' => 'paused',
                        'gray' => 'completed',
                    ]),
                    
                TextColumn::make('total_recipients')
                    ->label('Recipients')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('sent_count')
                    ->label('Sent')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('delivered_count')
                    ->label('Delivered')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('opened_count')
                    ->label('Opens')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                    
                TextColumn::make('clicked_count')
                    ->label('Clicks')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->toggleable(),
                    
                TextColumn::make('open_rate')
                    ->label('Open %')
                    ->formatStateUsing(fn ($record) => $record->open_rate . '%')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('click_rate')
                    ->label('Click %')
                    ->formatStateUsing(fn ($record) => $record->click_rate . '%')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('starts_at')
                    ->label('Scheduled')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'whatsapp' => 'WhatsApp',
                        'multi_channel' => 'Multi-Channel',
                    ]),
                    
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
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
