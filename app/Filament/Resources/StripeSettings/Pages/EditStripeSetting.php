<?php

namespace App\Filament\Resources\StripeSettings\Pages;

use App\Filament\Resources\StripeSettings\StripeSettingResource;
use App\Services\StripeIntegrationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStripeSetting extends EditRecord
{
    protected static string $resource = StripeSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verifyConnection')
                ->label('Verificar conexión')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->action(function (): void {
                    $record = $this->getRecord();
                    $ok = app(StripeIntegrationService::class)->verifyConnection($record);
                    $this->refreshFormData(['connection_status', 'last_verified_at', 'last_error_message']);

                    Notification::make()
                        ->title($ok ? 'Stripe conectado correctamente' : 'No se pudo verificar Stripe')
                        ->body($ok ? null : ($record->fresh()->last_error_message ?? 'Revisa las llaves.'))
                        ->{$ok ? 'success' : 'danger'}()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if (! $record->is_enabled) {
            $record->update([
                'connection_status' => 'disabled',
                'last_verified_at' => now(),
                'last_error_message' => null,
            ]);
        }
    }
}
