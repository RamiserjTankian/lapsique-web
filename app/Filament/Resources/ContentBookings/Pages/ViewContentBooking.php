<?php

namespace App\Filament\Resources\ContentBookings\Pages;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Models\SiteSetting;
use App\Services\ContentBookingDeliverablesService;
use App\Services\ContentBookingPaymentService;
use App\Services\CustomerPortalAccessService;
use App\Services\GoogleCalendarService;
use App\Services\StripeService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewContentBooking extends ViewRecord
{
    protected static string $resource = ContentBookingResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->record->loadMissing(['deliverableLinks', 'slot', 'customer']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addDeliverable')
                ->label('Añadir entregables')
                ->icon('heroicon-o-folder-plus')
                ->color('info')
                ->visible(fn () => in_array($this->record->status, ['confirmed', 'pending']))
                ->modalHeading('Añadir entregables')
                ->modalDescription('Se enviará un correo al cliente con el enlace de Google Drive. También quedará disponible en su portal.')
                ->form([
                    TextInput::make('label')
                        ->label('Nombre (opcional)')
                        ->placeholder('Ej: Reels, Fotos retocadas, Carpeta completa')
                        ->maxLength(120),

                    TextInput::make('url')
                        ->label('Enlace de Google Drive')
                        ->url()
                        ->required()
                        ->maxLength(2048)
                        ->placeholder('https://drive.google.com/drive/folders/...')
                        ->helperText('Puedes añadir varios enlaces; cada uno dispara un correo al cliente.'),
                ])
                ->action(function (array $data): void {
                    try {
                        app(ContentBookingDeliverablesService::class)->addDriveLink(
                            $this->record,
                            $data['url'],
                            $data['label'] ?? null,
                        );

                        $this->record->refresh();

                        Notification::make()
                            ->title('Entregable añadido')
                            ->body('Correo enviado a '.$this->record->client_email.' y visible en el portal.')
                            ->success()
                            ->send();
                    } catch (\InvalidArgumentException $e) {
                        Notification::make()
                            ->title('Enlace no válido')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            EditAction::make(),

            Action::make('confirm')
                ->label('Confirmar reserva')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['pending_payment', 'pending']))
                ->requiresConfirmation()
                ->action(function () {
                    app(ContentBookingPaymentService::class)->applyStatusTransition(
                        $this->record->fresh(),
                        'confirmed',
                        ['source' => 'admin_manual'],
                    );

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

            Action::make('syncStripe')
                ->label('Re-sincronizar Stripe')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->visible(fn () => $this->record->payment_provider === 'stripe' && filled($this->record->stripe_checkout_session_id))
                ->action(function () {
                    try {
                        $session = app(StripeService::class)->fetchSession((string) $this->record->stripe_checkout_session_id);
                        app(ContentBookingPaymentService::class)->syncStripeSession($this->record, $session);
                        Notification::make()->title('Pago sincronizado con Stripe')->success()->send();
                        $this->record->refresh();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error al sincronizar')->body($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('resendPortalAccess')
                ->label('Reenviar acceso al portal')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->visible(fn () => $this->record->customer !== null)
                ->requiresConfirmation()
                ->action(function () {
                    $booking = $this->record->fresh(['customer', 'slot']);
                    if ($booking->customer) {
                        app(CustomerPortalAccessService::class)->regeneratePortalAccessAndNotify(
                            $booking->customer,
                            booking: $booking,
                        );
                    }
                    Notification::make()->title('Acceso al portal reenviado')->success()->send();
                }),
        ];
    }
}
