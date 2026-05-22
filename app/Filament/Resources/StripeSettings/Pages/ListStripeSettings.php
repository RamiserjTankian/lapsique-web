<?php

namespace App\Filament\Resources\StripeSettings\Pages;

use App\Filament\Resources\StripeSettings\StripeSettingResource;
use App\Models\StripeSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStripeSettings extends ListRecords
{
    protected static string $resource = StripeSettingResource::class;

    public function mount(): void
    {
        parent::mount();

        if (StripeSetting::tableExists() && StripeSetting::query()->count() === 0) {
            $this->redirect(StripeSettingResource::getUrl('create'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Configurar Stripe')
                ->visible(fn (): bool => StripeSettingResource::canCreate()),
        ];
    }
}
