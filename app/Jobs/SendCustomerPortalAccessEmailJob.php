<?php

namespace App\Jobs;

use App\Mail\CustomerPortalAccessEmail;
use App\Models\ContactLog;
use App\Models\ContentBooking;
use App\Models\Customer;
use App\Models\EmailTracking;
use App\Models\TicketOrder;
use App\Services\MailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCustomerPortalAccessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(
        public Customer $customer,
        public string $temporaryPassword,
        public ?TicketOrder $order = null,
        public ?ContentBooking $booking = null,
    ) {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        $email = $this->customer->email;

        if (! $email) {
            Log::warning('Customer portal access email skipped: missing email', [
                'customer_id' => $this->customer->id,
            ]);
            return;
        }

        try {
            $trackingToken = EmailTracking::generateToken();

            $contactLog = ContactLog::create([
                'customer_id' => $this->customer->id,
                'event_id' => $this->order?->event_id,
                'channel' => 'email',
                'type' => 'transactional',
                'subject' => 'Acceso a tu portal de cliente',
                'message' => 'Credenciales iniciales del portal de cliente y sesiones',
                'metadata' => [
                    'template' => 'customer_portal_access',
                    'tracking_token' => $trackingToken,
                    'ticket_order_id' => $this->order?->id,
                    'content_booking_id' => $this->booking?->id,
                ],
                'status' => 'pending',
            ]);

            EmailTracking::create([
                'contact_log_id' => $contactLog->id,
                'customer_id' => $this->customer->id,
                'tracking_token' => $trackingToken,
            ]);

            $messageId = app(MailDeliveryService::class)->send(
                new CustomerPortalAccessEmail(
                    $this->customer,
                    $this->temporaryPassword,
                    $trackingToken,
                    $this->order?->load('event'),
                    $this->booking?->load('slot')
                ),
                $email,
                $this->customer->name,
                'customer-portal-access'
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
            Log::error('Failed to send customer portal access email', [
                'customer_id' => $this->customer->id,
                'error' => $exception->getMessage(),
            ]);

            if (isset($contactLog)) {
                $contactLog->markAsFailed($exception->getMessage());
            }

            throw $exception;
        }
    }
}
