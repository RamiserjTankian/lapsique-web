<?php

namespace App\Console\Commands;

use App\Models\BookingAvailabilityRule;
use App\Models\BookingSlot;
use App\Models\SiteSetting;
use App\Services\BookingAvailabilityRuleService;
use App\Services\BookingSlotGeneratorService;
use App\Services\GoogleCalendarService;
use Database\Seeders\BookingAvailabilityRulesSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EnsureBookingSlotsCommand extends Command
{
    protected $signature = 'booking:ensure-slots
        {--days= : Sobrescribe el periodo de generación (1-90 días)}
        {--without-calendar : Genera sin consultar conflictos de Google Calendar}';

    protected $description = 'Asegura reglas activas y genera horarios de booking sin duplicar slots existentes';

    public function handle(
        BookingSlotGeneratorService $generator,
        GoogleCalendarService $googleCalendar,
        BookingAvailabilityRuleService $rulesService,
    ): int {
        $availabilityDays = $this->validatedAvailabilityDays();

        if ($availabilityDays === false) {
            return self::FAILURE;
        }

        $addedRules = $rulesService->ensureDefaultRules();
        if ($addedRules > 0) {
            $this->info("Se añadieron {$addedRules} reglas de disponibilidad (2 PM y 5 PM).");
        }

        if (! BookingAvailabilityRule::query()->where('is_active', true)->exists()) {
            $this->warn('No hay reglas activas. Sembrando reglas por defecto...');
            $this->callSilent('db:seed', ['--class' => BookingAvailabilityRulesSeeder::class]);
            $this->info('Reglas por defecto creadas.');
        }

        $checkGoogleCalendar = false;

        if (! $this->option('without-calendar')) {
            try {
                $checkGoogleCalendar = $googleCalendar->isConnected();
            } catch (\Throwable $e) {
                $this->warn('Google Calendar no se pudo validar. Se generarán horarios sin revisar conflictos externos.');
                report($e);
            }
        }

        $result = $generator->generate(
            availabilityDays: $availabilityDays ?: null,
            checkGoogleCalendar: $checkGoogleCalendar,
        );

        $settings = SiteSetting::current();
        $windowDays = $availabilityDays ?: ($settings?->bookingAvailabilityDays()
            ?? (int) config('booking.availability_days', 11));
        $startTime = $settings?->bookingStartTime() ?? (string) config('booking.default_start_time', '14:00');
        $endTime = $settings?->bookingEndTime() ?? (string) config('booking.default_end_time', '17:00');
        $allowedTimeValues = config('booking.allowed_time_values', [$startTime, $endTime]);
        $timezone = (string) config('app.timezone', 'America/Cancun');
        $today = Carbon::today($timezone);
        $latestDate = $today->copy()->addDays($windowDays);
        $availableCount = BookingSlot::query()
            ->available()
            ->whereDate('date', '>=', $today->toDateString())
            ->whereDate('date', '<=', $latestDate->toDateString())
            ->where('time_value', '>=', $startTime)
            ->where('time_value', '<=', $endTime)
            ->whereIn('time_value', $allowedTimeValues)
            ->count();

        try {
            Cache::put('booking.last_slot_generation', array_merge($result, [
                'at' => now()->toIso8601String(),
                'available' => $availableCount,
                'timezone' => $timezone,
            ]), now()->addDays(7));
        } catch (\Throwable $e) {
            Log::warning('Booking slot generation status could not be cached.', [
                'error' => $e->getMessage(),
            ]);
        }

        $this->info(sprintf(
            'Horarios listos. Creados: %d | Omitidos: %d | Bloqueados por calendario: %d | Disponibles: %d',
            $result['created'],
            $result['skipped'],
            $result['blocked_by_calendar'],
            $availableCount,
        ));
        $this->line(sprintf(
            'Ventana: %s a %s · Zona horaria: %s',
            $today->toDateString(),
            $latestDate->toDateString(),
            $timezone,
        ));

        if ($availableCount === 0) {
            Log::error('Booking slot generation completed without public availability.', [
                'result' => $result,
                'window_start' => $today->toDateString(),
                'window_end' => $latestDate->toDateString(),
                'timezone' => $timezone,
            ]);
            $this->error('La agenda sigue sin horarios disponibles dentro de la ventana pública.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validatedAvailabilityDays(): int|false
    {
        $value = $this->option('days');

        if ($value === null) {
            return 0;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->error('La opción --days debe ser un número entero entre 1 y 90.');

            return false;
        }

        $days = (int) $value;

        if ($days < 1 || $days > 90) {
            $this->error('La opción --days debe estar entre 1 y 90.');

            return false;
        }

        return $days;
    }
}
