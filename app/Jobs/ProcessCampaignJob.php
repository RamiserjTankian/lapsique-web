<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Customer;
use App\Jobs\SendMarketingEmailJob;
use App\Jobs\SendSMSJob;
use App\Jobs\SendWhatsAppJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300; // 5 minutos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Campaign $campaign
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->campaign->status !== 'scheduled' && $this->campaign->status !== 'active') {
                Log::info('Campaign not ready to process', [
                    'campaign_id' => $this->campaign->id,
                    'status' => $this->campaign->status,
                ]);
                return;
            }

            // Marcar campaña como activa
            $this->campaign->update(['status' => 'active']);

            // Obtener destinatarios según la audiencia objetivo
            $recipients = $this->campaign->getRecipientsQuery()->get();
            
            // Actualizar total de destinatarios
            $this->campaign->update(['total_recipients' => $recipients->count()]);

            Log::info('Processing campaign', [
                'campaign_id' => $this->campaign->id,
                'total_recipients' => $recipients->count(),
                'type' => $this->campaign->type,
            ]);

            // Despachar jobs según el tipo de campaña
            foreach ($recipients as $customer) {
                $this->dispatchNotification($customer);
            }

            // Marcar como completada
            $this->campaign->markAsCompleted();

            Log::info('Campaign processed successfully', [
                'campaign_id' => $this->campaign->id,
                'total_recipients' => $recipients->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process campaign', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);

            $this->campaign->update(['status' => 'paused']);

            throw $e;
        }
    }

    /**
     * Despachar la notificación según el tipo de campaña
     */
    protected function dispatchNotification(Customer $customer): void
    {
        $options = [
            'campaign_id' => $this->campaign->id,
            'type' => 'marketing',
            'queue' => 'default',
        ];

        switch ($this->campaign->type) {
            case 'email':
                $content = $this->campaign->content['email'] ?? [];
                $options['button_text'] = $content['button_text'] ?? null;
                $options['button_url'] = $content['button_url'] ?? null;
                SendMarketingEmailJob::dispatch(
                    $customer,
                    $content['subject'] ?? 'Newsletter',
                    $content['body'] ?? '',
                    $options
                );
                break;

            case 'sms':
                $content = $this->campaign->content['sms'] ?? [];
                SendSMSJob::dispatch(
                    $customer,
                    $content['message'] ?? '',
                    $options
                );
                break;

            case 'whatsapp':
                $content = $this->campaign->content['whatsapp'] ?? [];
                SendWhatsAppJob::dispatch(
                    $customer,
                    $content['message'] ?? '',
                    $options
                );
                break;

            case 'multi_channel':
                // Enviar por múltiples canales
                if (!empty($this->campaign->content['email'])) {
                    $content = $this->campaign->content['email'];
                    $options['button_text'] = $content['button_text'] ?? null;
                    $options['button_url'] = $content['button_url'] ?? null;
                    SendMarketingEmailJob::dispatch(
                        $customer,
                        $content['subject'] ?? 'Newsletter',
                        $content['body'] ?? '',
                        $options
                    );
                }

                if (!empty($this->campaign->content['sms'])) {
                    $content = $this->campaign->content['sms'];
                    SendSMSJob::dispatch(
                        $customer,
                        $content['message'] ?? '',
                        $options
                    );
                }

                if (!empty($this->campaign->content['whatsapp'])) {
                    $content = $this->campaign->content['whatsapp'];
                    SendWhatsAppJob::dispatch(
                        $customer,
                        $content['message'] ?? '',
                        $options
                    );
                }
                break;
        }

        $this->campaign->incrementSent();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Campaign job failed permanently', [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);

        $this->campaign->update(['status' => 'paused']);
    }
}
