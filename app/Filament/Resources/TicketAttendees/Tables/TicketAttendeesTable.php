<?php

namespace App\Filament\Resources\TicketAttendees\Tables;

use App\Jobs\SendTicketAccessEmailJob;
use App\Models\TicketAttendee;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketAttendeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('event.title')
                    ->label('Evento')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('product.name')
                    ->label('Ticket')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => ['registered', 'checked_in'],
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('check_in_count')
                    ->label('Usos')
                    ->sortable(),
                TextColumn::make('check_in_limit')
                    ->label('Límite')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registro')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'title')
                    ->label('Evento'),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'registered' => 'Registrado',
                        'checked_in' => 'Check-in',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->actions([
                Action::make('qr')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('primary')
                    ->url(fn (TicketAttendee $record) => $record->getCheckInQrUrl())
                    ->openUrlInNewTab(),
                Action::make('send_qr')
                    ->label('Enviar QR')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (TicketAttendee $record) {
                        SendTicketAccessEmailJob::dispatchAfterResponse($record);

                        Notification::make()
                            ->title('Email enviado')
                            ->body('Se envió el QR al asistente.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }
}
