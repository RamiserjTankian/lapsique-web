<?php

namespace App\Services;

use App\Models\BookingAvailabilityRule;

class BookingAvailabilityRuleService
{
    /**
     * @return array<int, array{time_value: string, time_label: string}>
     */
    public static function defaultSlots(): array
    {
        return [
            ['time_value' => '14:00', 'time_label' => '2:00 PM'],
            ['time_value' => '15:00', 'time_label' => '3:00 PM'],
            ['time_value' => '16:00', 'time_label' => '4:00 PM'],
            ['time_value' => '17:00', 'time_label' => '5:00 PM'],
        ];
    }

    /**
     * Ensure Mon–Sun rules exist for 2:00–5:00 PM without removing custom rules.
     */
    public function ensureDefaultRules(): int
    {
        $created = 0;

        for ($dow = 1; $dow <= 7; $dow++) {
            foreach (self::defaultSlots() as $slot) {
                $rule = BookingAvailabilityRule::query()->firstOrCreate(
                    [
                        'day_of_week' => $dow,
                        'time_value' => $slot['time_value'],
                    ],
                    [
                        'time_label' => $slot['time_label'],
                        'max_bookings' => 1,
                        'is_active' => true,
                    ],
                );

                if ($rule->wasRecentlyCreated) {
                    $created++;
                } elseif (! $rule->is_active) {
                    $rule->update(['is_active' => true]);
                }
            }
        }

        return $created;
    }
}
