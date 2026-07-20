<?php

namespace App\Services;

use App\Jobs\SendContentBookingConfirmationEmailJob;
use App\Jobs\SendContentBookingReceiptEmailJob;
use App\Jobs\SendCustomerPortalAccessEmailJob;
use App\Models\ContentBooking;
use App\Models\SiteSetting;
use App\Services\Meta\MetaConversionsApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContentBookingPaymentService
{
    public function __construct(
        protected GoogleCalendarService $googleCalendar,
        protected CustomerPortalAccessService $portalAccess,
        protected StripeService $stripe,
    ) {}

    public function syncMercadoPagoPayment(ContentBooking $booking, array $payment): ContentBooking
    {
        return DB::transaction(function () use ($booking, $payment) {
            $booking->refresh();

            $mpStatus = (string) data_get($payment, 'status', '');
            $paymentId = (string) data_get($payment, 'id', '');

            $status = match ($mpStatus) {
                'approved' => 'confirmed',
                'pending', 'in_process', 'authorized' => 'pending',
                'rejected', 'cancelled', 'refunded', 'charged_back' => 'failed',
                default => $booking->status,
            };

            $booking->update([
                'payment_provider' => 'mercadopago',
                'mercadopago_payment_id' => $paymentId ?: $booking->mercadopago_payment_id,
                'mercadopago_status' => $mpStatus ?: $booking->mercadopago_status,
            ]);

            return $this->applyStatusTransition($booking->fresh(), $status, [
                'source' => 'mercadopago_webhook',
                'mp_status' => $mpStatus,
            ]);
        });
    }

    public function syncStripeSession(ContentBooking $booking, array $session): ContentBooking
    {
        $this->stripe->assertCheckoutSessionMatchesBooking($booking, $session);

        return DB::transaction(function () use ($booking, $session) {
            $booking->refresh();

            $paymentIntent = data_get($session, 'payment_intent', []);
            $paymentStatus = (string) data_get($session, 'payment_status');
            $intentStatus = is_array($paymentIntent) ? (string) data_get($paymentIntent, 'status') : '';
            $paymentIntentId = is_array($paymentIntent) ? (string) data_get($paymentIntent, 'id') : (string) $paymentIntent;

            $booking->update([
                'payment_provider' => 'stripe',
                'stripe_checkout_session_id' => (string) data_get($session, 'id'),
                'stripe_payment_intent_id' => $paymentIntentId ?: $booking->stripe_payment_intent_id,
                'stripe_status' => $intentStatus ?: $paymentStatus,
            ]);

            if ($paymentStatus === 'paid' || $intentStatus === 'succeeded') {
                return $this->applyStatusTransition($booking->fresh(), 'confirmed', [
                    'source' => 'stripe_session',
                ]);
            }

            if (in_array($intentStatus, ['processing', 'requires_action'], true) || $paymentStatus === 'unpaid') {
                return $this->applyStatusTransition($booking->fresh(), 'pending', [
                    'source' => 'stripe_session',
                ]);
            }

            if (in_array($intentStatus, ['canceled', 'requires_payment_method'], true)) {
                return $this->releaseSlotIfFailed($booking->fresh(), 'failed');
            }

            return $booking->fresh();
        });
    }

    public function syncStripePaymentIntent(ContentBooking $booking, array $intent): ContentBooking
    {
        return DB::transaction(function () use ($booking, $intent) {
            $booking->refresh();

            $status = (string) data_get($intent, 'status');

            $booking->update([
                'payment_provider' => 'stripe',
                'stripe_payment_intent_id' => (string) data_get($intent, 'id'),
                'stripe_status' => $status,
            ]);

            if ($status === 'succeeded') {
                return $this->applyStatusTransition($booking->fresh(), 'confirmed', [
                    'source' => 'stripe_payment_intent',
                ]);
            }

            if (in_array($status, ['processing', 'requires_action', 'requires_capture'], true)) {
                return $this->applyStatusTransition($booking->fresh(), 'pending', [
                    'source' => 'stripe_payment_intent',
                ]);
            }

            if (in_array($status, ['canceled', 'requires_payment_method'], true)) {
                return $this->releaseSlotIfFailed($booking->fresh(), 'failed');
            }

            return $booking->fresh();
        });
    }

    public function syncStripeRefund(ContentBooking $booking, array $charge): ContentBooking
    {
        return DB::transaction(function () use ($booking, $charge) {
            $booking->refresh();

            $intentId = (string) data_get($charge, 'payment_intent', '');

            $booking->update([
                'payment_provider' => 'stripe',
                'stripe_payment_intent_id' => $intentId !== '' ? $intentId : $booking->stripe_payment_intent_id,
                'stripe_status' => 'refunded',
            ]);

            if ($booking->status === 'confirmed') {
                $booking->slot?->decrement('booked_count');
                $booking->update(['status' => 'cancelled']);

                Log::info('ContentBooking refunded via Stripe', [
                    'booking_id' => $booking->id,
                    'public_id' => $booking->public_id,
                ]);

                return $booking->fresh(['slot', 'customer']);
            }

            return $this->releaseSlotIfFailed($booking->fresh(), 'cancelled');
        });
    }

    public function applyStatusTransition(ContentBooking $booking, string $status, array $context = []): ContentBooking
    {
        $wasConfirmed = $booking->status === 'confirmed';

        if ($status === 'confirmed') {
            if ($booking->status !== 'confirmed') {
                $booking->update([
                    'status' => 'confirmed',
                    'paid_at' => $booking->paid_at ?? now(),
                ]);
                $booking = $booking->fresh(['slot', 'customer']);
                $this->afterConfirmed($booking);
                app(CustomerJourneyInsightsService::class)->clearCache();
            }

            Log::info('ContentBooking confirmed', array_merge([
                'booking_id' => $booking->id,
            ], $context));

            return $booking->fresh(['slot', 'customer']);
        }

        if ($status === 'pending') {
            if ($booking->status === 'pending_payment') {
                $booking->update(['status' => 'pending']);
            }

            app(MetaConversionsApiService::class)->sendPaymentPendingForBooking($booking->fresh(['slot', 'customer']));
            app(CustomerJourneyInsightsService::class)->clearCache();

            return $booking->fresh(['slot', 'customer']);
        }

        if ($status === 'failed') {
            return $this->releaseSlotIfFailed($booking, 'failed');
        }

        if ($status !== $booking->status) {
            $booking->update(['status' => $status]);
        }

        return $booking->fresh(['slot', 'customer']);
    }

    public function releaseSlotIfFailed(ContentBooking $booking, string $status = 'failed'): ContentBooking
    {
        if (in_array($booking->status, ['pending_payment', 'pending'], true)) {
            $booking->slot?->decrement('booked_count');
        }

        $booking->update(['status' => $status]);
        app(MetaConversionsApiService::class)->sendPaymentFailedForBooking($booking->fresh(['slot', 'customer']));
        app(CustomerJourneyInsightsService::class)->clearCache();

        return $booking->fresh(['slot', 'customer']);
    }

    protected function afterConfirmed(ContentBooking $booking): void
    {
        app(MetaConversionsApiService::class)->sendPurchaseForBooking($booking);

        $this->createGoogleCalendarEvent($booking);

        $booking = $booking->fresh(['slot', 'customer']);
        $metadata = $booking->metadata ?? [];

        if (empty($metadata['receipt_email_sent'])) {
            SendContentBookingReceiptEmailJob::dispatchAfterResponse($booking);
            $metadata = $this->markBookingEmailSent($booking, 'receipt_email_sent');
        }

        if (empty($metadata['confirmation_email_sent'])) {
            SendContentBookingConfirmationEmailJob::dispatchAfterResponse($booking);
            $metadata = $this->markBookingEmailSent($booking, 'confirmation_email_sent');
        }

        if ($booking->customer) {
            $customer = $booking->customer;

            if ($customer->status !== 'customer') {
                $customer->update(['status' => 'customer']);
            }

            $password = $this->portalAccess->ensurePortalAccess($customer);

            if ($password && empty($metadata['portal_access_email_sent'])) {
                SendCustomerPortalAccessEmailJob::dispatchAfterResponse(
                    $customer->fresh(),
                    $password,
                    booking: $booking,
                );
                $this->markBookingEmailSent($booking, 'portal_access_email_sent');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function markBookingEmailSent(ContentBooking $booking, string $key): array
    {
        $metadata = $booking->metadata ?? [];
        $metadata[$key] = true;
        $booking->update(['metadata' => $metadata]);
        $booking->metadata = $metadata;

        return $metadata;
    }

    public function createGoogleCalendarEvent(ContentBooking $booking): void
    {
        if ($booking->google_calendar_event_id) {
            return;
        }

        try {
            if (! $this->googleCalendar->isConnected()) {
                return;
            }

            $settings = SiteSetting::current();
            $calendarId = $settings?->google_calendar_id ?? 'primary';
            $eventId = $this->googleCalendar->createBookingEvent($booking, $calendarId);

            if ($eventId) {
                $booking->update(['google_calendar_event_id' => $eventId]);
            }
        } catch (\Throwable $e) {
            Log::warning('Google Calendar event creation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
