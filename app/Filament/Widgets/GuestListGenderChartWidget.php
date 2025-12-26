<?php

namespace App\Filament\Widgets;

use App\Models\GuestListEntry;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class GuestListGenderChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    
    public ?string $heading = 'Registros por Género';

    protected int | string | array $columnSpan = 1;

    public ?string $filter = null;

    protected function getData(): array
    {
        $query = GuestListEntry::query();
        
        // Aplicar filtros si existen
        if ($this->filter === 'this_month') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        } elseif ($this->filter === 'this_year') {
            $query->whereYear('created_at', now()->year);
        }

        $men = (clone $query)->where('gender', 'masculino')->count();
        $women = (clone $query)->where('gender', 'femenino')->count();
        $other = (clone $query)->where('gender', 'otro')->count();
        $noGender = (clone $query)->whereNull('gender')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Registros',
                    'data' => [$men, $women, $other, $noGender],
                    'backgroundColor' => [
                        'rgb(59, 130, 246)', // Azul para hombres
                        'rgb(34, 197, 94)',  // Verde para mujeres
                        'rgb(168, 85, 247)', // Morado para otros
                        'rgb(156, 163, 175)', // Gris para sin género
                    ],
                ],
            ],
            'labels' => ['Hombres', 'Mujeres', 'Otros', 'Sin especificar'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Todos',
            'this_month' => 'Este mes',
            'this_year' => 'Este año',
        ];
    }
}

