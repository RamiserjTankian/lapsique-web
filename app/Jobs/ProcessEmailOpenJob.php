<?php

namespace App\Jobs;

use App\Models\EmailTracking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmailOpenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $trackingToken,
        public ?string $ipAddress = null,
        public ?string $userAgent = null
    ) {
        $this->onQueue('low'); // Baja prioridad para tracking
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $emailTracking = EmailTracking::where('tracking_token', $this->trackingToken)->first();

            if (!$emailTracking) {
                Log::warning('Email tracking not found', [
                    'tracking_token' => $this->trackingToken,
                ]);
                return;
            }

            // Registrar la apertura
            $emailTracking->recordOpen($this->ipAddress, $this->userAgent);

            Log::info('Email open processed', [
                'tracking_token' => $this->trackingToken,
                'customer_id' => $emailTracking->customer_id,
                'opens_count' => $emailTracking->opens_count,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process email open', [
                'tracking_token' => $this->trackingToken,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
