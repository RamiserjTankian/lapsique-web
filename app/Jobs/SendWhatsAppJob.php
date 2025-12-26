<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\TwilioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Customer $customer,
        public string $message,
        public array $options = []
    ) {
        $queue = $options['queue'] ?? 'default';
        $this->onQueue($queue);
    }

    /**
     * Execute the job.
     */
    public function handle(TwilioService $twilioService): void
    {
        try {
            $contactLog = $twilioService->sendWhatsApp(
                $this->customer,
                $this->message,
                $this->options
            );

            if ($contactLog) {
                Log::info('WhatsApp job completed successfully', [
                    'customer_id' => $this->customer->id,
                    'contact_log_id' => $contactLog->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp job failed', [
                'customer_id' => $this->customer->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('WhatsApp job failed permanently', [
            'customer_id' => $this->customer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
