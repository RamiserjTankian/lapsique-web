<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class TrascendentalCaseSeeder extends Seeder
{
    public function run(): void
    {
        $cases = [
            [
                'title' => 'Rebolledo - Zal Marina',
                'slug' => 'rebolledo-zal-marina',
                'headline' => 'Sold out con 21 dias de promocion.',
                'description' => 'Operacion completa para conectar un artista internacional con un venue premium y convertir una ventana corta de promocion en resultado medible.',
                'venue' => 'Zal Marina',
                'city' => 'Progreso, Yucatan',
                'case_summary' => 'Produccion integral, cashless y pauta para un evento sold out.',
                'case_metrics' => [
                    ['label' => 'Asistentes', 'value' => '450'],
                    ['label' => 'Resultado', 'value' => 'Sold out'],
                    ['label' => 'Promocion', 'value' => '21 dias'],
                ],
                'case_services' => ['Concept', 'Booking', 'Production', 'Execution', 'Marketing', 'Operations'],
                'case_sort' => 1,
            ],
            [
                'title' => 'Umi Fest - Tulum',
                'slug' => 'umi-fest-tulum',
                'headline' => 'Cuatro fechas curadas para un beach club.',
                'description' => 'Desarrollo de programacion, produccion y marketing para sostener varias fechas con una narrativa consistente.',
                'venue' => 'Beach club',
                'city' => 'Tulum',
                'case_summary' => 'Booking, curaduria, produccion y marketing para un formato de festival.',
                'case_metrics' => [
                    ['label' => 'Fechas', 'value' => '4'],
                    ['label' => 'Asistentes', 'value' => '~2000'],
                    ['label' => 'Formato', 'value' => 'Beach club'],
                ],
                'case_services' => ['Concept', 'Booking', 'Production', 'Execution', 'Marketing', 'Operations'],
                'case_sort' => 2,
            ],
        ];

        foreach ($cases as $case) {
            Event::query()->updateOrCreate(
                ['slug' => $case['slug']],
                [
                    ...$case,
                    'is_featured' => true,
                    'is_case_study' => true,
                    'priority' => $case['case_sort'],
                ],
            );
        }
    }
}
