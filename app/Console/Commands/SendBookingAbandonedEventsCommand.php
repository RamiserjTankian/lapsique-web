<?php

namespace App\Console\Commands;

use App\Models\ContentBooking;
use App\Services\Meta\MetaConversionsApiService;
use Illuminate\Console\Command;

class SendBookingAbandonedEventsCommand extends Command
{
    protected $signature = 'analytics:send-booking-abandoned-events {--minutes=60 : Pending-payment age before a booking is considered abandoned} {--limit=100}';

    protected $description = 'Send server-side BookingAbandoned CAPI events for old pending content or DJ set bookings.';

    public function handle(MetaConversionsApiService $meta): int
    {
        $minutes = max((int) $this->option('minutes'), 15);
        $limit = max((int) $this->option('limit'), 1);
        $sent = 0;

        ContentBooking::query()
            ->where('status', 'pending_payment')
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->where(function ($query): void {
                $query->whereNull('metadata->capi_bookingabandoned_sent')
                    ->orWhere('metadata->capi_bookingabandoned_sent', false);
            })
            ->oldest()
            ->limit($limit)
            ->get()
            ->each(function (ContentBooking $booking) use ($meta, &$sent): void {
                $meta->sendBookingAbandonedForBooking($booking);
                $sent++;
            });

        $this->info("BookingAbandoned events processed: {$sent}");

        return self::SUCCESS;
    }
}
