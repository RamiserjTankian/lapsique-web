<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Event;
use App\Models\GuestListEntry;
use App\Models\ContactLog;
use App\Models\EmailTracking;
use App\Mail\EventConfirmationEmail;
use App\Services\MailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEventConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GuestListEntry $guestListEntry
    ) {
        $this->onQueue('high'); // Alta prioridad
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $customer = $this->guestListEntry->customer;
        $event = $this->guestListEntry->event;

        if (! $customer || ! $event || ! $customer->email) {
            Log::warning('Event confirmation email skipped due to missing data', [
                'guest_list_entry_id' => $this->guestListEntry->id,
                'customer_id' => $this->guestListEntry->customer_id,
                'event_id' => $this->guestListEntry->event_id,
            ]);
            return;
        }

        try {
            // Generar token de tracking
            $trackingToken = EmailTracking::generateToken();

            // Crear log de contacto
            $contactLog = ContactLog::create([
                'customer_id' => $customer->id,
                'event_id' => $event->id,
                'channel' => 'email',
                'type' => 'transactional',
                'subject' => "Confirmación: {$event->title}",
                'message' => 'Confirmación de registro en guest list',
                'metadata' => [
                    'template' => 'event_confirmation',
                    'tracking_token' => $trackingToken,
                    'guest_list_entry_id' => $this->guestListEntry->id,
                ],
                'status' => 'pending',
            ]);

            // Crear registro de tracking
            $emailTracking = EmailTracking::create([
                'contact_log_id' => $contactLog->id,
                'customer_id' => $customer->id,
                'tracking_token' => $trackingToken,
            ]);

            // Enviar email
            $messageId = app(MailDeliveryService::class)->send(
                new EventConfirmationEmail($customer, $event, $this->guestListEntry, $trackingToken),
                $customer->email,
                $customer->name ?? null,
                'event-confirmation'
            );

            // Actualizar estado del log
            $contactLog->markAsSent();

            if ($messageId) {
                $contactLog->update([
                    'metadata' => array_merge($contactLog->metadata ?? [], [
                        'mailtrap_message_id' => $messageId,
                    ]),
                ]);
            }

            // Incrementar lead score
            $customer->incrementLeadScore(10);

            // Actualizar última interacción
            $customer->updateLastInteraction();

            Log::info('Event confirmation email sent successfully', [
                'customer_id' => $customer->id,
                'event_id' => $event->id,
                'guest_list_entry_id' => $this->guestListEntry->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send event confirmation email', [
                'customer_id' => $customer->id,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($contactLog)) {
                $contactLog->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Event confirmation email job failed permanently', [
            'guest_list_entry_id' => $this->guestListEntry->id,
            'customer_id' => $this->guestListEntry->customer_id,
            'event_id' => $this->guestListEntry->event_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
