<?php

namespace App\Console\Commands;

use App\Models\BookingAvailabilityRule;
use App\Services\BookingAvailabilityRuleService;
use App\Services\BookingSlotGeneratorService;
use App\Services\GoogleCalendarService;
use Database\Seeders\BookingAvailabilityRulesSeeder;
use Illuminate\Console\Command;

class EnsureBookingSlotsCommand extends Command
{
    protected $signature = 'booking:ensure-slots';

    protected $description = 'Asegura reglas activas y genera horarios de booking sin duplicar slots existentes';

    public function handle(
        BookingSlotGeneratorService $generator,
        GoogleCalendarService $googleCalendar,
        BookingAvailabilityRuleService $rulesService,
    ): int {
        $addedRules = $rulesService->ensureDefaultRules();
        if ($addedRules > 0) {
            $this->info("Se añadieron {$addedRules} reglas de disponibilidad (2 PM y 5 PM).");
        }

        if (! BookingAvailabilityRule::query()->where('is_active', true)->exists()) {
            $this->warn('No hay reglas activas. Sembrando reglas por defecto...');
            $this->callSilent('db:seed', ['--class' => BookingAvailabilityRulesSeeder::class]);
            $this->info('Reglas por defecto creadas.');
        }

        try {
            $checkGoogleCalendar = $googleCalendar->isConnected();
        } catch (\Throwable $e) {
            $checkGoogleCalendar = false;
            $this->warn('Google Calendar no se pudo validar. Se generarán horarios sin revisar conflictos externos.');
            report($e);
        }

        $result = $generator->generate(checkGoogleCalendar: $checkGoogleCalendar);

        $this->info(sprintf(
            'Horarios listos. Creados: %d | Omitidos: %d | Bloqueados por calendario: %d',
            $result['created'],
            $result['skipped'],
            $result['blocked_by_calendar'],
        ));

        return self::SUCCESS;
    }
}
