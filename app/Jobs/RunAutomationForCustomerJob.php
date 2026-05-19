<?php

namespace App\Jobs;

use App\Models\Automation;
use App\Models\Campaign;
use App\Models\ContactLog;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAutomationForCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $automationId,
        public int $customerId,
        public array $context = []
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $automation = Automation::find($this->automationId);
        $customer = Customer::find($this->customerId);

        if (! $automation || ! $customer) {
            return;
        }

        if (! $automation->isActive()) {
            return;
        }

        if (! $automation->shouldTriggerFor($customer, $this->context)) {
            return;
        }

        if ($this->alreadyTriggered($automation, $customer)) {
            return;
        }

        try {
            $automation->incrementTriggered();

            $delayMinutes = 0;
            $steps = is_array($automation->steps) ? $automation->steps : [];

            foreach ($steps as $index => $step) {
                $delayMinutes += (int) ($step['delay_minutes'] ?? 0);

                $action = $step['action'] ?? null;

                if ($action === 'send_campaign') {
                    $campaignId = $step['campaign_id'] ?? null;
                    $this->dispatchCampaignEmail($campaignId, $customer, $automation, $delayMinutes, $index);
                }
            }

            $automation->incrementCompleted();
        } catch (\Exception $e) {
            $automation->incrementFailed();

            Log::error('Automation execution failed', [
                'automation_id' => $automation->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function dispatchCampaignEmail(?int $campaignId, Customer $customer, Automation $automation, int $delayMinutes, int $stepIndex): void
    {
        if (! $campaignId) {
            return;
        }

        $campaign = Campaign::find($campaignId);

        if (! $campaign) {
            return;
        }

        if (! in_array($campaign->type, ['email', 'multi_channel'])) {
            return;
        }

        $content = $campaign->content['email'] ?? [];

        $options = [
            'campaign_id' => $campaign->id,
            'automation_id' => $automation->id,
            'button_text' => $content['button_text'] ?? null,
            'button_url' => $content['button_url'] ?? null,
            'metadata' => [
                'trigger' => 'email_opened',
                'source_campaign_id' => $this->context['campaign_id'] ?? null,
                'source_tracking_token' => $this->context['tracking_token'] ?? null,
                'automation_step' => $stepIndex + 1,
            ],
        ];

        $job = SendMarketingEmailJob::dispatch(
            $customer,
            $content['subject'] ?? 'Newsletter',
            $content['body'] ?? '',
            $options
        );

        if ($delayMinutes > 0) {
            $job->delay(now()->addMinutes($delayMinutes));
        }

        $campaign->incrementSent();
    }

    protected function alreadyTriggered(Automation $automation, Customer $customer): bool
    {
        $query = ContactLog::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id);

        $trackingToken = $this->context['tracking_token'] ?? null;
        $campaignId = $this->context['campaign_id'] ?? null;

        if ($trackingToken) {
            $query->where('metadata->source_tracking_token', $trackingToken);
        } elseif ($campaignId) {
            $query->where('metadata->source_campaign_id', $campaignId);
        }

        return $query->exists();
    }
}
