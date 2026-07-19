<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('meta:sync-campaign-insights')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('analytics:send-booking-abandoned-events --minutes=90 --limit=100')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('booking:ensure-slots')
    ->dailyAt('05:30')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->onFailure(function (): void {
        Log::error('Scheduled booking slot generation failed.', [
            'command' => 'booking:ensure-slots',
            'timezone' => config('app.timezone'),
        ]);
    });
