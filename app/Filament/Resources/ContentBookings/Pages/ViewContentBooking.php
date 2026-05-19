<?php

namespace App\Filament\Resources\ContentBookings\Pages;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Models\SiteSetting;
use App\Services\GoogleCalendarService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewContentBooking extends ViewRecord
{
    protected static string $resource = ContentBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('confirm')
                ->label('Confirmar reserva')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['pending_payment', 'pending']))
                ->requiresConfirmation()
                ->action(function () {
                    $wasConfirmed = $this->record->status === 'confirmed';
                    $this->record->update(['status' => 'confirmed']);

                    if (! $wasConfirmed && ! $this->record->google_calendar_event_id) {
                        try {
                            $googleCalendar = app(GoogleCalendarService::class);
                            if ($googleCalendar->isConnected()) {
                                $calendarId = SiteSetting::current()?->google_calendar_id ?? 'primary';
                                $eventId = $googleCalendar->createBookingEvent($this->record->fresh(), $calendarId);
                                if ($eventId) {
                                    $this->record->update(['google_calendar_event_id' => $eventId]);
                                }
                            }
                        } catch (\Throwable $e) {
                            // non-fatal
                        }
                    }

                    Notification::make()->title('Reserva confirmada')->success()->send();
                }),

            Action::make('cancel')
                ->label('Cancelar reserva')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => ! in_array($this->record->status, ['cancelled', 'failed']))
                ->requiresConfirmation()
                ->action(function () {
                    if (in_array($this->record->status, ['pending_payment', 'pending', 'confirmed'])) {
                        $this->record->slot?->decrement('booked_count');
                    }

                    // Delete Google Calendar event if it exists
                    if ($this->record->google_calendar_event_id) {
                        try {
                            $googleCalendar = app(GoogleCalendarService::class);
                            $calendarId = SiteSetting::current()?->google_calendar_id ?? 'primary';
                            $googleCalendar->deleteBookingEvent($this->record->google_calendar_event_id, $calendarId);
                        } catch (\Throwable $e) {
                            // non-fatal
                        }
                    }

                    $this->record->update(['status' => 'cancelled', 'google_calendar_event_id' => null]);
                    Notification::make()->title('Reserva cancelada')->success()->send();
                }),

            Action::make('publishDeliverables')
                ->label('Publicar entregables')
                ->icon('heroicon-o-folder-open')
                ->color('info')
                ->visible(fn () => $this->record->getMedia('deliverables')->isNotEmpty() && ! $this->record->deliverables_ready_at)
                ->action(function () {
                    $this->record->update(['deliverables_ready_at' => now()]);
                    Notification::make()->title('Entregables publicados en el portal')->success()->send();
                }),
        ];
    }
}
