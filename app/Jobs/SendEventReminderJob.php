<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\GuestListEntry;
use App\Models\ContactLog;
use App\Models\EmailTracking;
use App\Mail\EventReminderEmail;
use App\Services\MailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEventReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Event $event,
        public int $hoursBeforeEvent = 24
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Obtener todos los registros confirmados o pendientes
            $guestListEntries = $this->event->guestListEntries()
                ->whereIn('status', ['pending', 'confirmed'])
                ->with('customer')
                ->get();

            Log::info('Processing event reminders', [
                'event_id' => $this->event->id,
                'total_recipients' => $guestListEntries->count(),
                'hours_before' => $this->hoursBeforeEvent,
            ]);

            foreach ($guestListEntries as $entry) {
                $this->sendReminder($entry);
            }

            Log::info('Event reminders processed successfully', [
                'event_id' => $this->event->id,
                'total_sent' => $guestListEntries->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process event reminders', [
                'event_id' => $this->event->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function sendReminder(GuestListEntry $entry): void
    {
        $customer = $entry->customer;

        try {
            // Generar token de tracking
            $trackingToken = EmailTracking::generateToken();

            // Crear log de contacto
            $contactLog = ContactLog::create([
                'customer_id' => $customer->id,
                'event_id' => $this->event->id,
                'channel' => 'email',
                'type' => 'reminder',
                'subject' => "Recordatorio: {$this->event->title}",
                'message' => "Recordatorio del evento en {$this->hoursBeforeEvent} horas",
                'metadata' => [
                    'template' => 'event_reminder',
                    'tracking_token' => $trackingToken,
                    'guest_list_entry_id' => $entry->id,
                    'hours_before' => $this->hoursBeforeEvent,
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
                new EventReminderEmail(
                    $customer,
                    $this->event,
                    $entry,
                    $this->hoursBeforeEvent,
                    $trackingToken
                ),
                $customer->email,
                $customer->name ?? null,
                'event-reminder'
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

            // Actualizar última interacción
            $customer->updateLastInteraction();

            // Si el cliente tiene SMS/WhatsApp habilitado, también enviar por esos canales
            if ($customer->subscribed_sms && $customer->phone) {
                $smsMessage = "¡Hola! Te recordamos que mañana es {$this->event->title}. ¡Nos vemos!";
                SendSMSJob::dispatch($customer, $smsMessage, [
                    'event_id' => $this->event->id,
                    'type' => 'reminder',
                ]);
            }

            if ($customer->subscribed_whatsapp && $customer->whatsapp) {
                $whatsappMessage = "¡Hola {$customer->name}! 🎉\n\nTe recordamos que mañana es {$this->event->title}.\n\n¡Nos vemos en la pista! 🎧";
                SendWhatsAppJob::dispatch($customer, $whatsappMessage, [
                    'event_id' => $this->event->id,
                    'type' => 'reminder',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send event reminder', [
                'customer_id' => $customer->id,
                'event_id' => $this->event->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($contactLog)) {
                $contactLog->markAsFailed($e->getMessage());
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Event reminder job failed permanently', [
            'event_id' => $this->event->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
