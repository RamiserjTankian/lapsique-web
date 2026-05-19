<?php

namespace App\Filament\Resources\CustomerEventBalances\Pages;

use App\Filament\Resources\CustomerEventBalances\CustomerEventBalanceResource;
use App\Filament\Resources\CustomerEventBalances\Widgets\CustomerEventBalanceOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListCustomerEventBalances extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CustomerEventBalanceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerEventBalanceOverview::class,
        ];
    }
}
