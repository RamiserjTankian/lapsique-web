<?php

namespace App\Jobs;

use App\Mail\TicketOrderConfirmationEmail;
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

class SendTicketOrderConfirmationJob implements ShouldQueue
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
        $buyerEmail = $this->order->buyer_email;

        if (! $buyerEmail) {
            Log::warning('Ticket order confirmation email skipped: missing buyer email', [
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
                'type' => 'transactional',
                'subject' => "Confirmación de compra: {$this->order->event?->title}",
                'message' => 'Confirmación de compra de tickets',
                'metadata' => [
                    'template' => 'ticket_order_confirmation',
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
                new TicketOrderConfirmationEmail($this->order->load('items', 'event'), $trackingToken),
                $buyerEmail,
                $this->order->buyer_name,
                'ticket-order-confirmation'
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
            Log::error('Failed to send ticket order confirmation email', [
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
