<?php

namespace App\Console\Commands;

use App\Models\AnalyticsSession;
use App\Services\AnalyticsInsightsService;
use App\Services\AnalyticsSessionEnrichmentService;
use Illuminate\Console\Command;

class BackfillAnalyticsSessionMetadata extends Command
{
    protected $signature = 'analytics:backfill-session-metadata {--limit=500 : Numero maximo de sesiones a procesar}';

    protected $description = 'Rellena origen y geografia faltantes en sesiones de analiticas existentes';

    public function handle(AnalyticsSessionEnrichmentService $enrichmentService, AnalyticsInsightsService $analyticsInsightsService): int
    {
        $limit = max((int) $this->option('limit'), 1);

        $sessions = AnalyticsSession::query()
            ->where(function ($query) {
                $query->whereNull('source_type')
                    ->orWhereNull('source_label')
                    ->orWhereNull('country_name')
                    ->orWhereNull('region')
                    ->orWhereNull('region_code')
                    ->orWhereNull('city');
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('No hay sesiones pendientes de enriquecer.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($sessions->count());
        $bar->start();

        foreach ($sessions as $session) {
            $enrichmentService->enrich($session, $session->ip_address);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $analyticsInsightsService->clearCachedSnapshots();
        $this->info("Sesiones procesadas: {$sessions->count()}");

        return self::SUCCESS;
    }
}
