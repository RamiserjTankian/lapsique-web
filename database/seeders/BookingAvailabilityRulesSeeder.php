<?php

namespace Database\Seeders;

use App\Models\BookingAvailabilityRule;
use Illuminate\Database\Seeder;

class BookingAvailabilityRulesSeeder extends Seeder
{
    /**
     * Horarios disponibles: Lunes a Domingo, 2pm–8pm hora Cancún (UTC-5).
     * Se crea un slot por hora, de 14:00 a 20:00.
     */
    public function run(): void
    {
        // Limpiar reglas existentes antes de sembrar
        BookingAvailabilityRule::truncate();

        $days = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        // Horarios de 14:00 a 20:00 (2pm–8pm), un slot por hora
        $slots = [
            ['time_value' => '14:00', 'time_label' => '2:00 PM'],
            ['time_value' => '15:00', 'time_label' => '3:00 PM'],
            ['time_value' => '16:00', 'time_label' => '4:00 PM'],
            ['time_value' => '17:00', 'time_label' => '5:00 PM'],
            ['time_value' => '18:00', 'time_label' => '6:00 PM'],
            ['time_value' => '19:00', 'time_label' => '7:00 PM'],
            ['time_value' => '20:00', 'time_label' => '8:00 PM'],
        ];

        $rules = [];

        foreach ($days as $dow => $dayName) {
            foreach ($slots as $slot) {
                $rules[] = [
                    'day_of_week' => $dow,
                    'time_value' => $slot['time_value'],
                    'time_label' => $slot['time_label'],
                    'max_bookings' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        BookingAvailabilityRule::insert($rules);

        $this->command->info('✓ ' . count($rules) . ' reglas de disponibilidad creadas (Lun–Dom, 2pm–8pm).');
    }
}
