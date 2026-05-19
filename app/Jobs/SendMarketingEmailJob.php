<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\ContactLog;
use App\Models\EmailTracking;
use App\Mail\MarketingEmail;
use App\Services\MailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMarketingEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Customer $customer,
        public string $subject,
        public string $content,
        public array $options = []
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->customer->subscribed_newsletter) {
                Log::info('Customer not subscribed to newsletter', [
                    'customer_id' => $this->customer->id,
                ]);
                return;
            }

            // Generar token de tracking
            $trackingToken = EmailTracking::generateToken();

            // Crear log de contacto
            $metadata = array_merge($this->options['metadata'] ?? [], [
                'template' => 'marketing',
                'tracking_token' => $trackingToken,
            ]);

            $contactLog = ContactLog::create([
                'customer_id' => $this->customer->id,
                'campaign_id' => $this->options['campaign_id'] ?? null,
                'automation_id' => $this->options['automation_id'] ?? null,
                'channel' => 'email',
                'type' => 'marketing',
                'subject' => $this->subject,
                'message' => $this->content,
                'metadata' => $metadata,
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
                new MarketingEmail(
                    $this->customer,
                    $this->subject,
                    $this->content,
                    $trackingToken,
                    $this->options['button_text'] ?? null,
                    $this->options['button_url'] ?? null
                ),
                $this->customer->email,
                $this->customer->name ?? null,
                'marketing'
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

            // Actualizar métricas de la campaña si existe
            if (!empty($this->options['campaign_id'])) {
                $contactLog->campaign?->incrementDelivered();
            }

            Log::info('Marketing email sent successfully', [
                'customer_id' => $this->customer->id,
                'contact_log_id' => $contactLog->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send marketing email', [
                'customer_id' => $this->customer->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($contactLog)) {
                $contactLog->markAsFailed($e->getMessage());
                
                // Actualizar métricas de la campaña si existe
                if (!empty($this->options['campaign_id'])) {
                    $contactLog->campaign?->incrementFailed();
                }
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Marketing email job failed permanently', [
            'customer_id' => $this->customer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
