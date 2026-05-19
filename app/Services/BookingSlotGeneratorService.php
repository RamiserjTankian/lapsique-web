<?php

namespace App\Services;

use App\Models\BookingAvailabilityRule;
use App\Models\BookingSlot;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingSlotGeneratorService
{
    public function __construct(protected GoogleCalendarService $googleCalendar) {}

    /**
     * Generate booking slots for the next N days based on availability rules.
     * Skips dates that already have a slot. Optionally checks Google Calendar for conflicts.
     *
     * @return array{created: int, skipped: int, blocked_by_calendar: int}
     */
    public function generate(?int $availabilityDays = null, bool $checkGoogleCalendar = true): array
    {
        $settings = SiteSetting::current();
        $availabilityDays = $availabilityDays ?? ($settings?->bookingAvailabilityDays() ?? config('booking.availability_days', 11));
        $startTime = $settings?->bookingStartTime() ?? config('booking.default_start_time', '14:00');
        $endTime = $settings?->bookingEndTime() ?? config('booking.default_end_time', '17:00');
        $advanceHours = $settings?->booking_advance_hours ?? config('booking.default_advance_hours', 24);
        $durationMinutes = $settings?->bookingDurationMinutes() ?? config('booking.default_duration_minutes', 120);
        $calendarId = $settings?->google_calendar_id ?? 'primary';
        $timezone = config('app.timezone', 'America/Mexico_City');

        $rules = BookingAvailabilityRule::active()
            ->orderBy('day_of_week')
            ->orderBy('time_value')
            ->get();

        if ($rules->isEmpty()) {
            return ['created' => 0, 'skipped' => 0, 'blocked_by_calendar' => 0];
        }

        $gcalConnected = $checkGoogleCalendar && $this->googleCalendar->isConnected();

        $now = Carbon::now($timezone);
        $minAllowedDateTime = $now->copy()->addHours($advanceHours);
        $endDate = $now->copy()->addDays($availabilityDays)->endOfDay();

        // Pre-fetch busy times for the whole period if connected
        $busyTimes = [];
        if ($gcalConnected) {
            try {
                $busyTimes = $this->googleCalendar->getBusyTimes(
                    $calendarId,
                    $minAllowedDateTime->toDateTime(),
                    $endDate->toDateTime()
                );
            } catch (\Throwable $e) {
                Log::warning('BookingSlotGenerator: GCal freebusy failed, proceeding without it', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $created = 0;
        $skipped = 0;
        $blockedByCalendar = 0;

        // Walk each day from tomorrow until endDate
        $cursor = $now->copy()->startOfDay()->addDay();

        while ($cursor->lte($endDate)) {
            // ISO day: 1=Mon ... 7=Sun
            $isoDow = $cursor->dayOfWeekIso;

            // Get rules for this day of week
            $dayRules = $rules->where('day_of_week', $isoDow);

            foreach ($dayRules as $rule) {
                $ruleTime = substr((string) $rule->time_value, 0, 5);

                if ($ruleTime < $startTime || $ruleTime > $endTime) {
                    $skipped++;

                    continue;
                }

                [$hour, $minute] = array_map('intval', explode(':', $ruleTime));

                $slotDateTime = $cursor->copy()->setTime($hour, $minute, 0);

                // Skip past or too-soon slots
                if ($slotDateTime->lt($minAllowedDateTime)) {
                    $skipped++;

                    continue;
                }

                $dateStr = $slotDateTime->toDateString();

                // Skip if slot already exists
                $exists = BookingSlot::where('date', $dateStr)
                    ->where('time_value', $ruleTime)
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                // Check Google Calendar conflict
                if ($gcalConnected && ! empty($busyTimes)) {
                    $slotEnd = $slotDateTime->copy()->addMinutes($durationMinutes);

                    $hasConflict = collect($busyTimes)->contains(function ($busy) use ($slotDateTime, $slotEnd) {
                        return $slotDateTime->lt($busy['end']) && $slotEnd->gt($busy['start']);
                    });

                    if ($hasConflict) {
                        $blockedByCalendar++;
                        Log::debug('BookingSlotGenerator: slot blocked by Google Calendar', [
                            'date' => $dateStr,
                            'time' => $ruleTime,
                        ]);

                        continue;
                    }
                }

                // Create the slot
                BookingSlot::create([
                    'date' => $dateStr,
                    'time_label' => $rule->time_label,
                    'time_value' => $ruleTime,
                    'max_bookings' => $rule->max_bookings,
                    'booked_count' => 0,
                    'is_active' => true,
                ]);

                $created++;
            }

            $cursor->addDay();
        }

        Log::info('BookingSlotGenerator: generation complete', [
            'created' => $created,
            'skipped' => $skipped,
            'blocked_by_calendar' => $blockedByCalendar,
            'availability_days' => $availabilityDays,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return [
            'created' => $created,
            'skipped' => $skipped,
            'blocked_by_calendar' => $blockedByCalendar,
        ];
    }

    /**
     * Delete all future unbooked slots (to regenerate cleanly).
     */
    public function clearFutureSlots(): int
    {
        return BookingSlot::where('date', '>=', today())
            ->where('booked_count', 0)
            ->delete();
    }
}
