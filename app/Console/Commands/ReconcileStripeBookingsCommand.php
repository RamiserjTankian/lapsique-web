<?php

namespace App\Console\Commands;

use App\Models\ContentBooking;
use App\Services\ContentBookingPaymentService;
use App\Services\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcileStripeBookingsCommand extends Command
{
    protected $signature = 'stripe:reconcile-bookings {--limit=50 : Max bookings to reconcile}';

    protected $description = 'Re-sincroniza reservas Stripe pendientes consultando Checkout Sessions en Stripe';

    public function handle(
        StripeService $stripe,
        ContentBookingPaymentService $bookingPayment,
    ): int {
        $limit = (int) $this->option('limit');

        $bookings = ContentBooking::query()
            ->where('payment_provider', 'stripe')
            ->whereIn('status', ['pending_payment', 'pending'])
            ->whereNotNull('stripe_checkout_session_id')
            ->orderBy('updated_at')
            ->limit($limit)
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No hay reservas Stripe pendientes para conciliar.');

            return self::SUCCESS;
        }

        $confirmed = 0;
        $failed = 0;

        foreach ($bookings as $booking) {
            try {
                $session = $stripe->fetchSession((string) $booking->stripe_checkout_session_id);
                $updated = $bookingPayment->syncStripeSession($booking, $session);

                if ($updated->status === 'confirmed') {
                    $confirmed++;
                    $this->line("✓ {$booking->public_id} confirmada");
                } else {
                    $this->line("- {$booking->public_id} → {$updated->status}");
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('Stripe reconcile failed for booking', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
                $this->warn("✗ {$booking->public_id}: {$e->getMessage()}");
            }
        }

        $this->info("Conciliación lista. Confirmadas: {$confirmed} | Errores: {$failed}");

        return self::SUCCESS;
    }
}
