<?php

namespace App\Jobs;

use App\Mail\ContentBookingReceiptEmail;
use App\Models\ContactLog;
use App\Models\ContentBooking;
use App\Models\EmailTracking;
use App\Services\MailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendContentBookingReceiptEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 60;

    public function __construct(public ContentBooking $booking)
    {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        $booking = $this->booking->fresh(['slot', 'customer']);
        $email = $booking->client_email;

        if (! $email || ! $booking->customer_id) {
            Log::warning('Content booking receipt email skipped: missing recipient data', [
                'booking_id' => $booking->id,
                'has_email' => filled($email),
                'customer_id' => $booking->customer_id,
            ]);

            return;
        }

        try {
            $trackingToken = EmailTracking::generateToken();

            $contactLog = ContactLog::create([
                'customer_id' => $booking->customer_id,
                'channel' => 'email',
                'type' => 'transactional',
                'subject' => 'Recibo de tu reserva de '.$booking->service_short_name.' Lapsique',
                'message' => 'Recibo de pago de '.$booking->service_name,
                'metadata' => [
                    'template' => 'content_booking_receipt',
                    'tracking_token' => $trackingToken,
                    'content_booking_id' => $booking->id,
                ],
                'status' => 'pending',
            ]);

            EmailTracking::create([
                'contact_log_id' => $contactLog->id,
                'customer_id' => $booking->customer_id,
                'tracking_token' => $trackingToken,
            ]);

            $messageId = app(MailDeliveryService::class)->send(
                new ContentBookingReceiptEmail($booking, $trackingToken),
                $email,
                $booking->client_name,
                'content-booking-receipt',
            );

            $contactLog->markAsSent();

            if ($messageId) {
                $contactLog->update([
                    'metadata' => array_merge($contactLog->metadata ?? [], [
                        'mailtrap_message_id' => $messageId,
                    ]),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::error('Failed to send content booking receipt email', [
                'booking_id' => $booking->id,
                'error' => $exception->getMessage(),
            ]);

            if (isset($contactLog)) {
                $contactLog->markAsFailed($exception->getMessage());
            }

            throw $exception;
        }
    }
}
