<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\ContactLog;
use App\Models\EmailTracking;
use App\Mail\WelcomeEmail;
use App\Services\MailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [30, 60, 120]; // Retry after 30s, 60s, 120s

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Customer $customer,
        public array $options = []
    ) {
        $this->onQueue('high'); // Alta prioridad para emails de bienvenida
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Generar token de tracking
            $trackingToken = EmailTracking::generateToken();

            // Crear log de contacto
            $contactLog = ContactLog::create([
                'customer_id' => $this->customer->id,
                'channel' => 'email',
                'type' => 'transactional',
                'subject' => 'Bienvenido a Lapsique',
                'message' => 'Email de bienvenida para nuevo suscriptor',
                'metadata' => [
                    'template' => 'welcome',
                    'tracking_token' => $trackingToken,
                ],
                'status' => 'pending',
            ]);

            // Crear registro de tracking
            $emailTracking = EmailTracking::create([
                'contact_log_id' => $contactLog->id,
                'customer_id' => $this->customer->id,
                'tracking_token' => $trackingToken,
            ]);

            // Enviar email
            $messageId = app(MailDeliveryService::class)->send(
                new WelcomeEmail($this->customer, $trackingToken),
                $this->customer->email,
                $this->customer->name ?? null,
                'welcome'
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
            $this->customer->incrementLeadScore(5);

            Log::info('Welcome email sent successfully', [
                'customer_id' => $this->customer->id,
                'contact_log_id' => $contactLog->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'customer_id' => $this->customer->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($contactLog)) {
                $contactLog->markAsFailed($e->getMessage());
            }

            throw $e; // Re-throw para que Laravel maneje el retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Welcome email job failed permanently', [
            'customer_id' => $this->customer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
