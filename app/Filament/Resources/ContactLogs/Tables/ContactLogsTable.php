<?php

namespace App\Filament\Resources\ContactLogs\Tables;

use App\Services\MailtrapEventsService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class ContactLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                    
                BadgeColumn::make('channel')
                    ->colors([
                        'primary' => 'email',
                        'success' => 'sms',
                        'info' => 'whatsapp',
                        'warning' => 'call',
                        'secondary' => ['popup', 'guestlist', 'manual'],
                    ]),
                    
                BadgeColumn::make('type')
                    ->colors([
                        'danger' => 'transactional',
                        'warning' => 'notification',
                        'success' => 'marketing',
                        'info' => ['reminder', 'followup'],
                    ]),
                    
                TextColumn::make('subject')
                    ->limit(50)
                    ->searchable(),
                    
                BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'sent',
                        'success' => ['delivered', 'opened', 'clicked'],
                        'danger' => ['bounced', 'failed'],
                    ]),
                    
                TextColumn::make('sent_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                    
                TextColumn::make('opened_at')
                    ->dateTime('M d, Y H:i')
                    ->placeholder('—'),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'whatsapp' => 'WhatsApp',
                        'call' => 'Call',
                        'popup' => 'Popup',
                        'guestlist' => 'Guest List',
                        'manual' => 'Manual',
                    ]),
                    
                SelectFilter::make('type')
                    ->options([
                        'notification' => 'Notification',
                        'marketing' => 'Marketing',
                        'transactional' => 'Transactional',
                        'reminder' => 'Reminder',
                        'followup' => 'Follow-up',
                    ]),
                    
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'delivered' => 'Delivered',
                        'opened' => 'Opened',
                        'clicked' => 'Clicked',
                        'bounced' => 'Bounced',
                        'failed' => 'Failed',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ])
            ->headerActions([
                Action::make('sync_mailtrap')
                    ->label('Actualizar estados')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Actualizar estados de Mailtrap')
                    ->modalDescription('Se consultarán los últimos eventos y se recargará el estado de los correos.')
                    ->modalSubmitActionLabel('Actualizar')
                    ->action(function () {
                        Notification::make()
                            ->title('Actualizando...')
                            ->body('Consultando eventos de Mailtrap. Esto puede tardar unos segundos.')
                            ->info()
                            ->send();

                        try {
                            $processed = app(MailtrapEventsService::class)->sync();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al actualizar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                            return;
                        }

                        Notification::make()
                            ->title('Información recargada')
                            ->body("Se actualizaron {$processed} evento(s).")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
