<?php

namespace App\Filament\Pages;

use App\Services\MercadoPagoService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MercadoPagoConnection extends Page
{
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Mercado Pago';

    protected static ?string $title = 'Mercado Pago';

    protected static UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?string $navigationParentItem = 'Configuración';

    protected static ?int $navigationSort = 11;

    public array $connection = [];

    public function mount(MercadoPagoService $mercadoPago): void
    {
        $this->connection = $mercadoPago->getConnectionSummary();
    }

    public function getView(): string
    {
        return 'filament.pages.mercado-pago-connection';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect')
                ->label('Conectar OAuth')
                ->icon('heroicon-o-link')
                ->color('success')
                ->visible(fn (): bool => (bool) ($this->connection['storage_ready'] ?? false) && (bool) ($this->connection['oauth_ready'] ?? false) && (($this->connection['mode'] ?? null) !== 'oauth'))
                ->url(route('mercadopago.oauth.redirect')),
            Action::make('sync')
                ->label('Sincronizar cuenta')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn (): bool => (bool) ($this->connection['storage_ready'] ?? false) && (bool) ($this->connection['connected'] ?? false))
                ->action(function (MercadoPagoService $mercadoPago): void {
                    $mercadoPago->syncConnectionAccount();
                    $this->connection = $mercadoPago->getConnectionSummary();

                    Notification::make()
                        ->title('Cuenta sincronizada')
                        ->success()
                        ->send();
                }),
            Action::make('disconnect')
                ->label('Desconectar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => (bool) ($this->connection['storage_ready'] ?? false) && (bool) ($this->connection['connected'] ?? false) && ! ($this->connection['managed_by_env'] ?? false))
                ->action(function (MercadoPagoService $mercadoPago): void {
                    $mercadoPago->disconnect();
                    $this->connection = $mercadoPago->getConnectionSummary();

                    Notification::make()
                        ->title('Mercado Pago desconectado')
                        ->success()
                        ->send();
                }),
        ];
    }
}
