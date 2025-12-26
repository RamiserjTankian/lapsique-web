<?php

namespace App\Filament\Widgets;

use App\Models\GuestListScan;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GuestListScanStatusChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Estado de escaneos';

    protected int | string | array $columnSpan = 1;

    protected const STATUS_LABELS = [
        'checked_in' => 'Entradas',
        'duplicate' => 'Reescaneos',
        'limit_reached' => 'Limite alcanzado',
        'rejected' => 'Rechazados',
        'read' => 'Leidos',
    ];

    protected const STATUS_COLORS = [
        'checked_in' => 'rgb(34, 197, 94)',
        'duplicate' => 'rgb(234, 179, 8)',
        'limit_reached' => 'rgb(239, 68, 68)',
        'rejected' => 'rgb(148, 163, 184)',
        'read' => 'rgb(59, 130, 246)',
    ];

    public ?int $eventId = null;

    protected function getData(): array
    {
        $eventId = $this->getEventId();

        $rows = GuestListScan::query()
            ->select('scan_status', DB::raw('count(*) as total'))
            ->when($eventId, fn (Builder $query) => $query->whereHas('guestListEntry', fn (Builder $subQuery) => $subQuery->where('event_id', $eventId)))
            ->groupBy('scan_status')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $totals = [];
        $colors = [];

        foreach ($rows as $row) {
            $status = $row->scan_status;
            $labels[] = self::STATUS_LABELS[$status] ?? ucfirst(str_replace('_', ' ', $status));
            $totals[] = $row->total;
            $colors[] = self::STATUS_COLORS[$status] ?? 'rgb(107, 114, 128)';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Escaneos',
                    'data' => $totals,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getEventId(): ?int
    {
        $filters = $this->pageFilters ?? [];
        $eventId = $this->eventId ?? ($filters['event_id'] ?? null);

        if (! $eventId) {
            return null;
        }

        return (int) $eventId;
    }
}
