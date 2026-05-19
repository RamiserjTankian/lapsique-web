<?php

namespace App\Jobs;

use App\Mail\TicketProspectEmail;
use App\Models\ContactLog;
use App\Models\EmailTracking;
use App\Models\TicketOrder;
use App\Services\MailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTicketProspectEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(
        public TicketOrder $order
    ) {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        if (! $this->order->buyer_email) {
            Log::warning('Ticket prospect email skipped: missing buyer email', [
                'order_id' => $this->order->id,
            ]);

            return;
        }

        try {
            $trackingToken = EmailTracking::generateToken();

            $contactLog = ContactLog::create([
                'customer_id' => $this->order->customer_id,
                'event_id' => $this->order->event_id,
                'channel' => 'email',
                'type' => 'notification',
                'subject' => 'Completa tu compra en lapsique.media',
                'message' => 'Bienvenida con newsletter y recordatorio de compra pendiente',
                'metadata' => [
                    'template' => 'ticket_prospect',
                    'tracking_token' => $trackingToken,
                    'ticket_order_id' => $this->order->id,
                ],
                'status' => 'pending',
            ]);

            EmailTracking::create([
                'contact_log_id' => $contactLog->id,
                'customer_id' => $this->order->customer_id,
                'tracking_token' => $trackingToken,
            ]);

            $messageId = app(MailDeliveryService::class)->send(
                new TicketProspectEmail($this->order->load('event', 'items'), $trackingToken),
                $this->order->buyer_email,
                $this->order->buyer_name,
                'ticket-prospect'
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
            Log::error('Failed to send ticket prospect email', [
                'order_id' => $this->order->id,
                'error' => $exception->getMessage(),
            ]);

            if (isset($contactLog)) {
                $contactLog->markAsFailed($exception->getMessage());
            }

            throw $exception;
        }
    }
}
