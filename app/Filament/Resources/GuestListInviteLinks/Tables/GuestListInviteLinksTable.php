<?php

namespace App\Filament\Resources\GuestListInviteLinks\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuestListInviteLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->withCount([
                    'guestListEntries as confirmed_count' => function ($q) {
                        $q->where('status', 'confirmed');
                    },
                    'guestListEntries as attended_count' => function ($q) {
                        $q->where('status', 'attended');
                    },
                    'guestListEntries as men_count' => function ($q) {
                        $q->where('gender', 'male');
                    },
                    'guestListEntries as women_count' => function ($q) {
                        $q->where('gender', 'female');
                    },
                ]);
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event.title')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dj.name')
                    ->label('DJ')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                TextColumn::make('rp.name')
                    ->label('RP')
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('current_registrations')
                    ->label('Registros')
                    ->getStateUsing(function ($record) {
                        $current = $record->current_registrations;
                        $max = $record->max_registrations;
                        $percentage = $max > 0 ? round(($current / $max) * 100, 1) : 0;
                        $confirmed = $record->confirmed_count ?? 0;
                        $attended = $record->attended_count ?? 0;
                        $men = $record->men_count ?? 0;
                        $women = $record->women_count ?? 0;
                        
                        $main = $current . ($max ? ' / ' . $max . ' (' . $percentage . '%)' : '');
                        $parts = [];
                        if ($confirmed > 0) $parts[] = "✓ {$confirmed} conf";
                        if ($attended > 0) $parts[] = "✓ {$attended} asist";
                        if ($men > 0 || $women > 0) $parts[] = "{$men}H/{$women}M";
                        
                        return $main . ($parts ? ' • ' . implode(' • ', $parts) : '');
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->max_registrations) return 'success';
                        $percentage = ($record->current_registrations / $record->max_registrations) * 100;
                        if ($percentage >= 100) return 'danger';
                        if ($percentage >= 80) return 'warning';
                        return 'success';
                    })
                    ->sortable()
                    ->wrap(),
                TextColumn::make('expires_at')
                    ->label('Expira')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('copy_link')
                    ->label('Copiar Link')
                    ->icon('heroicon-o-clipboard')
                    ->color('success')
                    ->action(function ($record) {
                        $url = $record->invite_url;
                        
                        Notification::make()
                            ->title('Link copiado')
                            ->success()
                            ->body('El link completo ha sido copiado al portapapeles')
                            ->send();
                    })
                    ->requiresConfirmation(false)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => 'navigator.clipboard.writeText("' . addslashes($record->invite_url) . '")',
                    ]),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
