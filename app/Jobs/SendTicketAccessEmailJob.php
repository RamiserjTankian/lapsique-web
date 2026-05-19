<?php

namespace App\Jobs;

use App\Mail\TicketAccessEmail;
use App\Models\ContactLog;
use App\Models\EmailTracking;
use App\Models\TicketAttendee;
use App\Services\MailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTicketAccessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(
        public TicketAttendee $attendee
    ) {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        $email = $this->attendee->email;

        if (! $email) {
            Log::warning('Ticket access email skipped: missing attendee email', [
                'attendee_id' => $this->attendee->id,
            ]);
            return;
        }

        try {
            $trackingToken = EmailTracking::generateToken();

            $contactLog = ContactLog::create([
                'customer_id' => $this->attendee->customer_id,
                'event_id' => $this->attendee->event_id,
                'channel' => 'email',
                'type' => 'transactional',
                'subject' => 'Acceso de ticket confirmado',
                'message' => 'Envío de QR para acceso con ticket',
                'metadata' => [
                    'template' => 'ticket_access',
                    'tracking_token' => $trackingToken,
                    'ticket_attendee_id' => $this->attendee->id,
                ],
                'status' => 'pending',
            ]);

            EmailTracking::create([
                'contact_log_id' => $contactLog->id,
                'customer_id' => $this->attendee->customer_id,
                'tracking_token' => $trackingToken,
            ]);

            $messageId = app(MailDeliveryService::class)->send(
                new TicketAccessEmail($this->attendee->load('event', 'product', 'order'), $trackingToken),
                $email,
                $this->attendee->name,
                'ticket-access'
            );

            $contactLog->markAsSent();

            if ($messageId) {
                $contactLog->update([
                    'metadata' => array_merge($contactLog->metadata ?? [], [
                        'mailtrap_message_id' => $messageId,
                    ]),
                ]);
            }

            if ($this->attendee->customer) {
                $this->attendee->customer->incrementLeadScore(20);
                $this->attendee->customer->updateLastInteraction();
            }
        } catch (\Throwable $exception) {
            Log::error('Failed to send ticket access email', [
                'attendee_id' => $this->attendee->id,
                'error' => $exception->getMessage(),
            ]);

            if (isset($contactLog)) {
                $contactLog->markAsFailed($exception->getMessage());
            }

            throw $exception;
        }
    }
}
