<?php

namespace App\Jobs;

use App\Mail\ContentBookingDeliverablesReadyEmail;
use App\Models\ContactLog;
use App\Models\ContentBooking;
use App\Models\ContentBookingDeliverableLink;
use App\Models\EmailTracking;
use App\Services\MailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendContentBookingDeliverablesReadyEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 60;

    public function __construct(
        public ContentBooking $booking,
        public ContentBookingDeliverableLink $deliverableLink,
    ) {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        $booking = $this->booking->fresh(['slot', 'customer', 'deliverableLinks']);
        $deliverableLink = $this->deliverableLink->fresh();

        if (! $booking || ! $deliverableLink) {
            return;
        }

        $email = $booking->client_email;

        if (! $email) {
            Log::warning('Content booking deliverables email skipped: missing email', [
                'booking_id' => $booking->id,
                'deliverable_link_id' => $deliverableLink->id,
            ]);

            return;
        }

        if ($deliverableLink->notified_at) {
            return;
        }

        try {
            $trackingToken = EmailTracking::generateToken();

            $contactLog = ContactLog::create([
                'customer_id' => $booking->customer_id,
                'channel' => 'email',
                'type' => 'transactional',
                'subject' => 'Tu contenido Lapsique ya está listo',
                'message' => 'Entregables de '.$booking->service_short_name.' publicados',
                'metadata' => [
                    'template' => 'content_booking_deliverables_ready',
                    'tracking_token' => $trackingToken,
                    'content_booking_id' => $booking->id,
                    'deliverable_link_id' => $deliverableLink->id,
                ],
                'status' => 'pending',
            ]);

            EmailTracking::create([
                'contact_log_id' => $contactLog->id,
                'customer_id' => $booking->customer_id,
                'tracking_token' => $trackingToken,
            ]);

            $messageId = app(MailDeliveryService::class)->send(
                new ContentBookingDeliverablesReadyEmail($booking, $deliverableLink, $trackingToken),
                $email,
                $booking->client_name,
                'content-booking-deliverables-ready',
            );

            $contactLog->markAsSent();

            if ($messageId) {
                $contactLog->update([
                    'metadata' => array_merge($contactLog->metadata ?? [], [
                        'mailtrap_message_id' => $messageId,
                    ]),
                ]);
            }

            $deliverableLink->update(['notified_at' => now()]);
        } catch (\Throwable $exception) {
            Log::error('Failed to send content booking deliverables email', [
                'booking_id' => $booking->id,
                'deliverable_link_id' => $deliverableLink->id,
                'error' => $exception->getMessage(),
            ]);

            if (isset($contactLog)) {
                $contactLog->markAsFailed($exception->getMessage());
            }

            throw $exception;
        }
    }
}
