<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Computed;

class CampaignStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $record = $this->getRecord();
        
        if (!$record) {
            return [];
        }

        return [
            Stat::make('Total Destinatarios', $record->total_recipients)
                ->description('Emails enviados')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('primary'),
            
            Stat::make('Tasa de Apertura', $record->open_rate . '%')
                ->description($record->opened_count . ' aperturas')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
            
            Stat::make('Tasa de Clicks', $record->click_rate . '%')
                ->description($record->clicked_count . ' clicks')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('warning'),
            
            Stat::make('Click-to-Open', $record->click_to_open_rate . '%')
                ->description('De los que abrieron')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
    
    protected function getRecord(): ?Campaign
    {
        // Obtener el record del contexto de la página
        $livewire = $this->getLivewire();
        if (method_exists($livewire, 'getRecord')) {
            return $livewire->getRecord();
        }
        return null;
    }
}

