<?php

namespace App\Jobs;

use App\Mail\ContentBookingConfirmationEmail;
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

class SendContentBookingConfirmationEmailJob implements ShouldQueue
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

        if (! $email) {
            Log::warning('Content booking confirmation email skipped: missing email', [
                'booking_id' => $booking->id,
            ]);

            return;
        }

        try {
            $trackingToken = EmailTracking::generateToken();

            $contactLog = ContactLog::create([
                'customer_id' => $booking->customer_id,
                'channel' => 'email',
                'type' => 'transactional',
                'subject' => 'Tu sesión Lapsique está confirmada',
                'message' => 'Confirmación de reserva de sesión de contenido',
                'metadata' => [
                    'template' => 'content_booking_confirmation',
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
                new ContentBookingConfirmationEmail($booking, $trackingToken),
                $email,
                $booking->client_name,
                'content-booking-confirmation',
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
            Log::error('Failed to send content booking confirmation email', [
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
