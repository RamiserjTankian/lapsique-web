<?php

namespace App\Filament\Resources\GuestListEntries\Tables;

use App\Jobs\SendEventConfirmationJob;
use App\Models\GuestListEntry;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;

class GuestListEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->with(['inviteLink.event', 'inviteLink.dj', 'inviteLink.rp', 'customer', 'event', 'dj', 'rp']);
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('event.title')
                    ->label('Evento')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('customer.instagram_handle')
                    ->label('Instagram')
                    ->searchable()
                    ->url(fn ($record) => $record->customer && $record->customer->instagram_handle 
                        ? 'https://instagram.com/' . ltrim($record->customer->instagram_handle, '@')
                        : null)
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-camera')
                    ->color('primary')
                    ->toggleable(),
                TextColumn::make('dj.name')
                    ->label('DJ')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('rp.name')
                    ->label('RP')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('inviteLink.name')
                    ->label('Link de Invitación')
                    ->getStateUsing(function ($record) {
                        if (!$record->inviteLink) {
                            return 'Manual';
                        }
                        
                        $link = $record->inviteLink;
                        $parts = [];
                        
                        if ($link->name) {
                            $parts[] = $link->name;
                        }
                        
                        if ($link->dj) {
                            $parts[] = 'DJ: ' . $link->dj->name;
                        } elseif ($link->rp) {
                            $parts[] = 'RP: ' . $link->rp->name;
                        }
                        
                        return $parts ? implode(' | ', $parts) : 'Link General';
                    })
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn ($record) => $record->inviteLink ? 'Click para copiar: ' . $record->inviteLink->invite_url : null)
                    ->extraAttributes(function ($record) {
                        if (!$record->inviteLink) {
                            return [];
                        }
                        $url = $record->inviteLink->invite_url;
                        return [
                            'x-data' => '{ copied: false }',
                            'x-on:click' => "navigator.clipboard.writeText('" . addslashes($url) . "').then(() => { copied = true; setTimeout(() => copied = false, 2000); })",
                            'class' => 'cursor-pointer',
                            'style' => 'user-select: none;',
                        ];
                    }),
                TextColumn::make('gender')
                    ->label('Género')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('plus_ones')
                    ->label('Acompañantes')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => ['confirmed', 'attended'],
                        'danger' => ['cancelled', 'no_show'],
                    ]),
                TextColumn::make('check_in_at')
                    ->label('Check-in')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('check_in_count')
                    ->label('Usos')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('check_in_limit')
                    ->label('Límite')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Registro')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('event')
                    ->relationship('event', 'title')
                    ->label('Evento'),
                SelectFilter::make('dj')
                    ->relationship('dj', 'name')
                    ->label('DJ'),
                SelectFilter::make('rp')
                    ->relationship('rp', 'name')
                    ->label('RP'),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'attended' => 'Asistió',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'No asistió',
                    ]),
                SelectFilter::make('invite_link_id')
                    ->relationship('inviteLink', 'name')
                    ->label('Link de Invitación')
                    ->searchable(),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->fileName('guest-list-entries')
                    ->timeFormat('m-d-Y'),
            ])
            ->actions([
                Action::make('qr')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('primary')
                    ->url(fn (GuestListEntry $record) => $record->getCheckInQrUrl())
                    ->openUrlInNewTab(),
                Action::make('send_qr')
                    ->label('Enviar QR')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (GuestListEntry $record) {
                        SendEventConfirmationJob::dispatchAfterResponse($record);

                        Notification::make()
                            ->title('Email enviado')
                            ->body('Se envió la confirmación con el QR al invitado.')
                            ->success()
                            ->send();
                    }),
                Action::make('copy_link')
                    ->label('Copiar Link')
                    ->icon('heroicon-o-clipboard')
                    ->color('success')
                    ->visible(fn ($record) => $record->inviteLink !== null)
                    ->action(function ($record) {
                        if ($record->inviteLink) {
                            $url = $record->inviteLink->invite_url;
                            
                            // Copiar al portapapeles usando JavaScript
                            Notification::make()
                                ->title('Link copiado')
                                ->success()
                                ->body('El link completo ha sido copiado al portapapeles')
                                ->send();
                            
                            return $url;
                        }
                    })
                    ->requiresConfirmation(false)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => $record->inviteLink ? 'navigator.clipboard.writeText("' . addslashes($record->inviteLink->invite_url) . '")' : '',
                    ]),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_send_qr')
                        ->label('Enviar QR')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                SendEventConfirmationJob::dispatchAfterResponse($record);
                            }

                            Notification::make()
                                ->title('Emails enviados')
                                ->body('Se enviaron los QR a los invitados seleccionados.')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
