<?php

namespace App\Observers;

use App\Models\TicketOrder;
use App\Services\Meta\MetaConversionsApiService;

class TicketOrderObserver
{
    public function updated(TicketOrder $order): void
    {
        if (! $order->wasChanged('status') || $order->status !== 'paid') {
            return;
        }

        app(MetaConversionsApiService::class)->sendPurchaseForTicketOrder($order->fresh());
    }
}
