<?php

namespace App\Filament\Resources\BookingAvailabilityRules\Pages;

use App\Filament\Resources\BookingAvailabilityRules\BookingAvailabilityRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookingAvailabilityRules extends ListRecords
{
    protected static string $resource = BookingAvailabilityRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
