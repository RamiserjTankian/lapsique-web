<?php

namespace Tests\Feature;

use App\Models\BookingAvailabilityRule;
use App\Models\BookingSlot;
use App\Services\BookingSlotGeneratorService;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class BookingSlotPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.timezone' => 'America/Cancun']);
        Carbon::setTestNow(Carbon::create(2026, 7, 18, 10, 0, 0, 'America/Cancun'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_scheduled_generation_uses_the_booking_timezone_every_day(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event): bool => str_contains($event->command ?? '', 'booking:ensure-slots'));

        $this->assertNotNull($event);
        $this->assertSame('30 5 * * *', $event->expression);
        $this->assertSame('America/Cancun', $event->timezone);
    }

    public function test_command_bootstraps_rules_and_publishes_a_complete_window(): void
    {
        $this->artisan('booking:ensure-slots', [
            '--days' => 3,
            '--without-calendar' => true,
        ])
            ->expectsOutputToContain('Disponibles: 6')
            ->expectsOutputToContain('Zona horaria: America/Cancun')
            ->assertSuccessful();

        $this->assertSame(14, BookingAvailabilityRule::query()->where('is_active', true)->count());
        $this->assertSame(6, BookingSlot::query()->count());
        $this->assertSame('2026-07-19', BookingSlot::query()->oldest('date')->firstOrFail()->date->toDateString());
        $this->assertSame('2026-07-21', BookingSlot::query()->latest('date')->firstOrFail()->date->toDateString());
    }

    public function test_generation_is_idempotent_and_rolls_the_window_forward(): void
    {
        $arguments = [
            '--days' => 3,
            '--without-calendar' => true,
        ];

        $this->artisan('booking:ensure-slots', $arguments)->assertSuccessful();
        $this->artisan('booking:ensure-slots', $arguments)->assertSuccessful();

        $this->assertSame(6, BookingSlot::query()->count());

        Carbon::setTestNow(Carbon::create(2026, 7, 19, 10, 0, 0, 'America/Cancun'));

        $this->artisan('booking:ensure-slots', $arguments)
            ->expectsOutputToContain('Disponibles: 8')
            ->assertSuccessful();

        $this->assertSame(8, BookingSlot::query()->count());
        $this->assertDatabaseHas('booking_slots', [
            'date' => '2026-07-22 00:00:00',
            'time_value' => '17:00',
        ]);
        $this->assertSame(0, BookingSlot::query()
            ->selectRaw('date, time_value, count(*) as aggregate')
            ->groupBy('date', 'time_value')
            ->havingRaw('count(*) > 1')
            ->count());
    }

    public function test_generator_applies_advance_notice_in_cancun_local_time(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 7, 18, 23, 30, 0, 'America/Cancun'));

        for ($day = 1; $day <= 7; $day++) {
            foreach ([['14:00', '2:00 PM'], ['17:00', '5:00 PM']] as [$value, $label]) {
                BookingAvailabilityRule::query()->create([
                    'day_of_week' => $day,
                    'time_value' => $value,
                    'time_label' => $label,
                    'max_bookings' => 1,
                    'is_active' => true,
                ]);
            }
        }

        $result = app(BookingSlotGeneratorService::class)->generate(
            availabilityDays: 2,
            checkGoogleCalendar: false,
        );

        $this->assertSame(2, $result['created']);
        $this->assertSame(['2026-07-20'], BookingSlot::query()
            ->orderBy('date')
            ->pluck('date')
            ->map->toDateString()
            ->unique()
            ->values()
            ->all());
    }

    public function test_generation_continues_when_calendar_connection_check_is_unavailable(): void
    {
        foreach ([['14:00', '2:00 PM'], ['17:00', '5:00 PM']] as [$value, $label]) {
            BookingAvailabilityRule::query()->create([
                'day_of_week' => 7,
                'time_value' => $value,
                'time_label' => $label,
                'max_bookings' => 1,
                'is_active' => true,
            ]);
        }

        $calendar = $this->mock(GoogleCalendarService::class);
        $calendar->shouldReceive('isConnected')
            ->once()
            ->andThrow(new RuntimeException('calendar unavailable'));

        $result = app(BookingSlotGeneratorService::class)->generate(
            availabilityDays: 1,
            checkGoogleCalendar: true,
        );

        $this->assertSame(2, $result['created']);
        $this->assertSame(2, BookingSlot::query()->count());
    }

    public function test_command_fails_loudly_when_no_public_slot_can_be_generated(): void
    {
        config(['booking.allowed_time_values' => []]);

        $this->artisan('booking:ensure-slots', [
            '--days' => 3,
            '--without-calendar' => true,
        ])
            ->expectsOutputToContain('La agenda sigue sin horarios disponibles')
            ->assertFailed();

        $this->assertSame(0, BookingSlot::query()->count());
    }

    public function test_command_rejects_an_invalid_generation_window(): void
    {
        $this->artisan('booking:ensure-slots', [
            '--days' => 0,
            '--without-calendar' => true,
        ])
            ->expectsOutputToContain('La opción --days debe estar entre 1 y 90.')
            ->assertFailed();

        $this->assertSame(0, BookingAvailabilityRule::query()->count());
    }
}
