<?php

namespace App\Filament\Widgets;

use App\Models\GuestListEntry;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class GuestListRpChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    
    public ?string $heading = 'Registros por RP';

    protected int | string | array $columnSpan = 1;

    public ?string $filter = null;

    protected function getData(): array
    {
        $query = GuestListEntry::query()->whereNotNull('rp_id');
        
        // Aplicar filtros si existen
        if ($this->filter === 'this_month') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        } elseif ($this->filter === 'this_year') {
            $query->whereYear('created_at', now()->year);
        }

        $stats = $query
            ->select('rp_id', DB::raw('count(*) as total'))
            ->groupBy('rp_id')
            ->with('rp:id,name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $labels = $stats->map(fn($item) => $item->rp?->name ?? 'Sin RP')->toArray();
        $data = $stats->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Registros',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)',
                        'rgb(251, 146, 60)',
                        'rgb(236, 72, 153)',
                        'rgb(14, 165, 233)',
                        'rgb(20, 184, 166)',
                        'rgb(245, 158, 11)',
                        'rgb(139, 92, 246)',
                        'rgb(239, 68, 68)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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

