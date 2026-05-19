<?php

namespace App\Filament\Support;

use App\Models\CustomerEventBalance;
use App\Models\TicketOrder;
use App\Services\TicketOrderService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class CustomerEventBalanceCancelSaleAction
{
    public static function make(string $name = 'cancel_balance_sale'): Action
    {
        return Action::make($name)
            ->label('Cancelar venta')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(function (CustomerEventBalance $record): bool {
                $order = $record->lastTicketOrder;

                return $order instanceof TicketOrder
                    && in_array($order->status, ['paid', 'pending'], true);
            })
            ->requiresConfirmation()
            ->modalHeading('Cancelar venta')
            ->modalDescription('Se revertirá el saldo acreditado y también quedarán anulados los tickets de acceso ligados a esta venta. Esta acción no procesa un reembolso externo en Mercado Pago o Stripe.')
            ->modalSubmitActionLabel('Cancelar venta')
            ->form([
                Textarea::make('reason')
                    ->label('Motivo')
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('Ejemplo: venta de prueba o cargo duplicado'),
            ])
            ->action(function (CustomerEventBalance $record, array $data): void {
                $order = $record->lastTicketOrder;

                if (! $order) {
                    Notification::make()
                        ->title('No hay venta vinculada')
                        ->body('Este saldo ya no tiene una orden activa para cancelar.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    app(TicketOrderService::class)->cancelOrderFromAdmin(
                        $order,
                        $data['reason'] ?? null,
                        auth()->id()
                    );

                    Notification::make()
                        ->title('Venta cancelada')
                        ->body('La venta fue cancelada, el saldo se revirtió y los accesos del ticket quedaron anulados.')
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
