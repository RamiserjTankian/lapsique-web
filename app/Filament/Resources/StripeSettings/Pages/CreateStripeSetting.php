<?php

namespace App\Filament\Resources\StripeSettings\Pages;

use App\Filament\Resources\StripeSettings\StripeSettingResource;
use App\Services\StripeIntegrationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateStripeSetting extends CreateRecord
{
    protected static string $resource = StripeSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return StripeSettingResource::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $ok = app(StripeIntegrationService::class)->verifyConnection($record);

        Notification::make()
            ->title($ok ? 'Configuración creada y conexión verificada' : 'Configuración creada; verifica las llaves')
            ->body($ok ? null : ($record->fresh()->last_error_message ?? null))
            ->{$ok ? 'success' : 'warning'}()
            ->send();
    }
}
