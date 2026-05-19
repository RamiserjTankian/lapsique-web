<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\EmailTracking;
use Filament\Widgets\ChartWidget;

class CampaignClicksChartWidget extends ChartWidget
{
    protected ?string $heading = 'Clicks por Día';

    public ?Campaign $record = null;

    protected function getData(): array
    {
        $record = $this->getRecord();
        
        if (!$record) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Obtener clicks de los últimos 30 días
        $clicks = EmailTracking::query()
            ->whereHas('contactLog', function ($query) use ($record) {
                $query->where('campaign_id', $record->id);
            })
            ->whereNotNull('first_clicked_at')
            ->selectRaw('DATE(first_clicked_at) as date, COUNT(*) as count')
            ->where('first_clicked_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            
            $click = $clicks->firstWhere('date', $date);
            $data[] = $click ? $click->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Clicks',
                    'data' => $data,
                    'backgroundColor' => 'rgba(251, 146, 60, 0.2)',
                    'borderColor' => 'rgb(251, 146, 60)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getRecord(): ?Campaign
    {
        if ($this->record) {
            return $this->record;
        }

        // Intentar obtener del componente padre (ViewRecord)
        try {
            $owner = $this->getOwner();
            if ($owner && method_exists($owner, 'getRecord')) {
                return $owner->getRecord();
            }
            if ($owner && property_exists($owner, 'record')) {
                return $owner->record;
            }
        } catch (\Exception $e) {
            // Ignorar errores
        }

        return null;
    }
}

