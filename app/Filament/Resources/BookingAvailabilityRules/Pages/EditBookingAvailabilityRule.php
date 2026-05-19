<?php

namespace App\Filament\Resources\BookingAvailabilityRules\Pages;

use App\Filament\Resources\BookingAvailabilityRules\BookingAvailabilityRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBookingAvailabilityRule extends EditRecord
{
    protected static string $resource = BookingAvailabilityRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
