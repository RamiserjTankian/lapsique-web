<?php

namespace App\Filament\Resources\SessionCustomers\Pages;

use App\Filament\Resources\SessionCustomers\SessionCustomerResource;
use App\Filament\Resources\SessionCustomers\Support\SessionCustomerModalActions;
use App\Filament\Resources\SessionCustomers\Widgets\SessionCustomersOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListSessionCustomers extends ListRecords
{
    protected static string $resource = SessionCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SessionCustomerModalActions::create(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SessionCustomersOverviewWidget::class,
        ];
    }
}
