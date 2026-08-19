<?php

namespace App\Console\Commands;

use App\Models\TicketOrder;
use App\Services\TicketOrderService;
use Illuminate\Console\Command;

class ExpireAbandonedTicketReservations extends Command
{
    protected $signature = 'tickets:expire-abandoned-reservations {--minutes=30} {--limit=200}';

    protected $description = 'Release pending ticket reservations that never reached a payment provider';

    public function handle(TicketOrderService $orders): int
    {
        $minutes = max((int) $this->option('minutes'), 5);
        $limit = min(max((int) $this->option('limit'), 1), 1000);
        $expired = 0;

        TicketOrder::query()
            ->where('status', 'pending')
            ->whereNull('mp_payment_id')
            ->whereNull('mp_preference_id')
            ->whereNull('stripe_session_id')
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->where('metadata->reservation_status', 'reserved')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (TicketOrder $order) use ($orders, &$expired): void {
                if ($orders->expireAbandonedReservation($order)->status === 'cancelled') {
                    $expired++;
                }
            });

        $this->info("Expired {$expired} abandoned ticket reservation(s).");

        return self::SUCCESS;
    }
}
