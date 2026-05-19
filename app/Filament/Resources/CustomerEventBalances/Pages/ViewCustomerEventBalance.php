<?php

namespace App\Filament\Resources\CustomerEventBalances\Pages;

use App\Filament\Resources\CustomerEventBalances\CustomerEventBalanceResource;
use App\Filament\Resources\TicketOrders\TicketOrderResource;
use App\Filament\Support\CustomerEventBalanceCancelSaleAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewCustomerEventBalance extends ViewRecord
{
    protected static string $resource = CustomerEventBalanceResource::class;

    public function infolist(Schema $schema): Schema
    {
        return CustomerEventBalanceResource::infolist($schema);
    }

    protected function getHeaderActions(): array
    {
        $order = $this->getRecord()->lastTicketOrder;

        return [
            Action::make('view_order')
                ->label('Ver orden')
                ->icon('heroicon-o-receipt-percent')
                ->url($order ? TicketOrderResource::getUrl('view', ['record' => $order]) : null)
                ->visible((bool) $order),
            CustomerEventBalanceCancelSaleAction::make(),
        ];
    }
}
