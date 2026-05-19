<?php

namespace App\Filament\Support;

use App\Models\TicketOrder;
use App\Services\TicketOrderService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class TicketOrderCancelAction
{
    public static function make(string $name = 'cancel_sale'): Action
    {
        return Action::make($name)
            ->label('Cancelar venta')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (TicketOrder $record): bool => in_array($record->status, ['paid', 'pending'], true))
            ->requiresConfirmation()
            ->modalHeading('Cancelar venta')
            ->modalDescription('La venta se quitara del evento y del historial de ventas pagadas. Esta accion no procesa un reembolso externo en Mercado Pago o Stripe.')
            ->modalSubmitActionLabel('Cancelar venta')
            ->form([
                Textarea::make('reason')
                    ->label('Motivo')
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('Ejemplo: venta de prueba'),
            ])
            ->action(function (TicketOrder $record, array $data): void {
                try {
                    app(TicketOrderService::class)->cancelOrderFromAdmin(
                        $record,
                        $data['reason'] ?? null,
                        auth()->id()
                    );

                    Notification::make()
                        ->title('Venta cancelada')
                        ->body('La venta fue removida del evento y del historial de ventas pagadas.')
                        ->success()
                        ->send();
                } catch (\Throwable $exception) {
                    Notification::make()
                        ->title('No se pudo cancelar la venta')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
