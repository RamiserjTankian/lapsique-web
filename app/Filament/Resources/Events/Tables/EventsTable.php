<?php

namespace App\Filament\Resources\Events\Tables;

use App\Jobs\SendEventConfirmationJob;
use App\Models\ContactLog;
use App\Models\Event;
use App\Models\GuestListEntry;
use App\Services\MailtrapEventsService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->label('Cover')
                    ->collection('cover')
                    ->square()
                    ->size(64),
                TextColumn::make('title')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Fecha')
                    ->dateTime('d M Y - H:i')
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Ciudad')
                    ->sortable(),
                TextColumn::make('rps_count')
                    ->label('RPs')
                    ->counts('rps')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                TextColumn::make('guests_count')
                    ->label('Guest List')
                    ->counts('guests')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('is_featured')
                    ->options([
                        1 => 'Destacado',
                        0 => 'Normal',
                    ]),
            ])
            ->recordActions([
                Action::make('send_qr_all')
                    ->label('Enviar QR a todos')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Event $record) {
                        $sent = 0;
                        $skipped = 0;

                        GuestListEntry::query()
                            ->where('event_id', $record->id)
                            ->with('customer')
                            ->chunkById(200, function ($entries) use (&$sent, &$skipped) {
                                foreach ($entries as $entry) {
                                    if (! $entry->customer || ! $entry->customer->email) {
                                        $skipped++;
                                        continue;
                                    }

                                    SendEventConfirmationJob::dispatchAfterResponse($entry);
                                    $sent++;
                                }
                            });

                        Notification::make()
                            ->title('Emails enviados')
                            ->body("Se enviaron {$sent} QR. Omitidos: {$skipped}.")
                            ->success()
                            ->send();
                    }),
                Action::make('resend_qr_pending')
                    ->label('Reenviar pendientes')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reenviar QR pendientes')
                    ->modalDescription('Se consultará Mailtrap y se reenviarán los QR pendientes o rebotados.')
                    ->modalSubmitActionLabel('Reenviar')
                    ->action(function (Event $record) {
                        Notification::make()
                            ->title('Actualizando estados...')
                            ->body('Consultando eventos de Mailtrap para obtener pendientes.')
                            ->info()
                            ->send();

                        try {
                            app(MailtrapEventsService::class)->sync();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al actualizar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                            return;
                        }

                        $latestLogs = ContactLog::query()
                            ->where('channel', 'email')
                            ->where('type', 'transactional')
                            ->where('event_id', $record->id)
                            ->where('metadata->template', 'event_confirmation')
                            ->orderByDesc('created_at')
                            ->get(['id', 'customer_id', 'status', 'created_at'])
                            ->unique('customer_id');

                        $statusByCustomer = $latestLogs
                            ->pluck('status', 'customer_id')
                            ->all();

                        $pendingStatuses = ['pending', 'failed', 'bounced'];
                        $resent = 0;
                        $skipped = 0;
                        $pendingEmails = [];

                        GuestListEntry::query()
                            ->where('event_id', $record->id)
                            ->with('customer')
                            ->chunkById(200, function ($entries) use (&$resent, &$skipped, &$pendingEmails, $statusByCustomer, $pendingStatuses) {
                                foreach ($entries as $entry) {
                                    if (! $entry->customer || ! $entry->customer->email) {
                                        $skipped++;
                                        continue;
                                    }

                                    $status = $statusByCustomer[$entry->customer_id] ?? null;

                                    if ($status && ! in_array($status, $pendingStatuses, true)) {
                                        continue;
                                    }

                                    $pendingEmails[] = $entry->customer->email;
                                    SendEventConfirmationJob::dispatchAfterResponse($entry);
                                    $resent++;
                                }
                            });

                        if ($resent === 0) {
                            Notification::make()
                                ->title('Sin pendientes')
                                ->body('No hay correos pendientes por reenviar para este evento.')
                                ->success()
                                ->send();
                            return;
                        }

                        $preview = array_slice(array_unique($pendingEmails), 0, 5);
                        $extra = max(count(array_unique($pendingEmails)) - count($preview), 0);
                        $listPreview = $preview ? implode(', ', $preview) : '';

                        Notification::make()
                            ->title('Reenvío programado')
                            ->body("Se reenviaron {$resent} QR. Omitidos: {$skipped}." . ($listPreview ? " Pendientes: {$listPreview}" : '') . ($extra ? " y {$extra} más." : ''))
                            ->success()
                            ->send();
                    }),
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
