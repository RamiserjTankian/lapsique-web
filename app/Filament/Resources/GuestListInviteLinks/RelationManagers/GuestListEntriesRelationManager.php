<?php

namespace App\Filament\Resources\GuestListInviteLinks\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Jobs\SendEventConfirmationJob;
use App\Models\GuestListEntry;

class GuestListEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'guestListEntries';

    protected static ?string $title = 'Registros de Guest List';

    protected static ?string $recordTitleAttribute = 'customer.name';


    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->with(['customer', 'event', 'dj', 'rp']);
            })
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('customer.phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('event.title')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => ['confirmed', 'attended'],
                        'danger' => ['cancelled', 'no_show'],
                    ])
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Género')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('check_in_at')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
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
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'attended' => 'Asistió',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'No asistió',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No hay registros aún')
            ->emptyStateDescription('Los registros aparecerán aquí cuando alguien se registre usando este link.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->fileName('guest-list-entrances')
                    ->timeFormat('m-d-Y'),
            ])
            ->actions([
                Action::make('send_qr')
                    ->label('Enviar QR')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (GuestListEntry $record) {
                        if (! $record->customer || ! $record->customer->email) {
                            Notification::make()
                                ->title('Email faltante')
                                ->body('El invitado no tiene un email registrado.')
                                ->warning()
                                ->send();
                            return;
                        }

                        SendEventConfirmationJob::dispatchAfterResponse($record);

                        Notification::make()
                            ->title('Email enviado')
                            ->body('Se envió la confirmación con el QR al invitado.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                Action::make('mark_fraudulent')
                    ->label('Marcar como Fraudulento')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Marcar registro como fraudulento')
                    ->modalDescription('¿Estás seguro de que este registro es fraudulento? Se eliminará y se restará el uso del link.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->action(function ($record) {
                        $record->markAsFraudulent();
                        
                        Notification::make()
                            ->title('Registro eliminado')
                            ->success()
                            ->body('El registro ha sido marcado como fraudulento y eliminado. El uso del link ha sido restado.')
                            ->send();
                    }),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar registro')
                    ->modalDescription('¿Estás seguro de eliminar este registro? Se restará el uso del link.')
                    ->action(function ($record) {
                        // Restar el contador del link al eliminar
                        if ($record->inviteLink) {
                            $record->inviteLink->decrementRegistrations();
                        }
                        
                        $record->delete();
                        
                        Notification::make()
                            ->title('Registro eliminado')
                            ->success()
                            ->body('El registro ha sido eliminado y el uso del link ha sido restado.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_send_qr')
                        ->label('Enviar QR')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $sent = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                if (! $record->customer || ! $record->customer->email) {
                                    $skipped++;
                                    continue;
                                }

                                SendEventConfirmationJob::dispatchAfterResponse($record);
                                $sent++;
                            }

                            Notification::make()
                                ->title('Emails enviados')
                                ->body("Se enviaron {$sent} QR. Omitidos: {$skipped}.")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar registros seleccionados')
                        ->modalDescription('¿Estás seguro de eliminar estos registros? Se restará el uso del link para cada registro eliminado.')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                // Restar el contador del link al eliminar
                                if ($record->inviteLink) {
                                    $record->inviteLink->decrementRegistrations();
                                }
                                $record->delete();
                                $count++;
                            }
                            
                            Notification::make()
                                ->title('Registros eliminados')
                                ->success()
                                ->body("Se eliminaron {$count} registro(s). El uso del link ha sido restado.")
                                ->send();
                        }),
                ]),
            ]);
    }
}
