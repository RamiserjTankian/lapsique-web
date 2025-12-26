<?php

namespace App\Filament\Widgets;

use App\Models\GuestListScan;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GuestListAccessIntervalChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Entradas vs reescaneos por intervalo';

    protected int | string | array $columnSpan = 1;

    protected const CHECKED_IN_STATUS = 'checked_in';
    protected const RESCAN_STATUSES = ['duplicate', 'limit_reached'];

    public ?int $eventId = null;
    public ?int $intervalMinutes = null;
    public ?string $timezone = null;

    protected function getData(): array
    {
        $eventId = $this->getEventId();
        $intervalMinutes = $this->getIntervalMinutes();

        $scans = GuestListScan::query()
            ->select(['scanned_at', 'scan_status'])
            ->when($eventId, fn (Builder $query) => $query->whereHas('guestListEntry', fn (Builder $subQuery) => $subQuery->where('event_id', $eventId)))
            ->whereIn('scan_status', array_merge([self::CHECKED_IN_STATUS], self::RESCAN_STATUSES))
            ->orderBy('scanned_at')
            ->get();

        $buckets = $this->buildIntervalBuckets($scans, $intervalMinutes);

        return [
            'datasets' => [
                [
                    'label' => 'Entradas',
                    'data' => array_column($buckets, 'checked_in'),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.6)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Reescaneos',
                    'data' => array_column($buckets, 'rescans'),
                    'backgroundColor' => 'rgba(234, 179, 8, 0.6)',
                    'borderColor' => 'rgb(234, 179, 8)',
                ],
            ],
            'labels' => array_column($buckets, 'label'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array | RawJs | null
    {
        return [
            'responsive' => true,
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
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

    protected function getIntervalMinutes(): int
    {
        $filters = $this->pageFilters ?? [];
        $intervalMinutes = (int) ($this->intervalMinutes ?? ($filters['interval_minutes'] ?? 30));

        return $this->normalizeIntervalMinutes($intervalMinutes);
    }

    protected function getTimezone(): string
    {
        $filters = $this->pageFilters ?? [];
        $timezone = $this->timezone ?? ($filters['timezone'] ?? 'America/Cancun');

        return $timezone ?: 'America/Cancun';
    }

    protected function normalizeIntervalMinutes(int $intervalMinutes): int
    {
        $allowed = [15, 30, 60];

        return in_array($intervalMinutes, $allowed, true) ? $intervalMinutes : 30;
    }

    protected function buildIntervalBuckets(Collection $scans, int $intervalMinutes): array
    {
        if ($scans->isEmpty()) {
            return [];
        }

        $intervalMinutes = $this->normalizeIntervalMinutes($intervalMinutes);
        $firstScanAt = $this->inTimezone($scans->first()->scanned_at);
        $lastScanAt = $this->inTimezone($scans->last()->scanned_at);
        $useDate = $firstScanAt && $lastScanAt
            ? $firstScanAt->toDateString() !== $lastScanAt->toDateString()
            : false;
        $labelFormat = $useDate ? 'd/m H:i' : 'H:i';

        $buckets = [];

        foreach ($scans as $scan) {
            $scanTime = $this->inTimezone($scan->scanned_at);
            if (! $scanTime) {
                continue;
            }

            $bucketStart = $this->normalizeTimeToInterval($scanTime, $intervalMinutes);
            $bucketKey = $bucketStart->timestamp;

            if (! isset($buckets[$bucketKey])) {
                $buckets[$bucketKey] = [
                    'label' => $bucketStart->format($labelFormat),
                    'checked_in' => 0,
                    'rescans' => 0,
                ];
            }

            if ($scan->scan_status === self::CHECKED_IN_STATUS) {
                $buckets[$bucketKey]['checked_in']++;
            } else {
                $buckets[$bucketKey]['rescans']++;
            }
        }

        ksort($buckets);

        return array_values($buckets);
    }

    protected function normalizeTimeToInterval(Carbon $time, int $intervalMinutes): Carbon
    {
        $intervalMinutes = $this->normalizeIntervalMinutes($intervalMinutes);
        $minute = (int) $time->format('i');
        $bucketMinute = intdiv($minute, $intervalMinutes) * $intervalMinutes;

        return $time->copy()->setTime((int) $time->format('H'), $bucketMinute, 0);
    }

    protected function inTimezone(?Carbon $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        return $value->copy()->setTimezone($this->getTimezone());
    }
}
