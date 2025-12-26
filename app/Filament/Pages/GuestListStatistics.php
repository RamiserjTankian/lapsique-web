<?php

namespace App\Filament\Pages;

use App\Jobs\SendEventConfirmationJob;
use App\Models\Event;
use App\Models\GuestListEntry;
use App\Models\GuestListScan;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use BackedEnum;
use UnitEnum;

class GuestListStatistics extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected string $view = 'filament.pages.guest-list-statistics';
    protected static ?string $navigationLabel = 'Estadísticas Guest List';
    protected static ?string $title = 'Estadísticas de Guest List';
    protected static UnitEnum|string|null $navigationGroup = 'Eventos';
    protected static ?int $navigationSort = 3;

    public array $filters = [];

    protected const DEFAULT_TIMEZONE = 'America/Cancun';
    protected const CHECKED_IN_STATUS = 'checked_in';
    protected const RESCAN_STATUSES = ['duplicate', 'limit_reached'];

    public function mount(): void
    {
        $this->filters['event_id'] ??= $this->getDefaultEventId();
        $this->filters['timezone'] ??= self::DEFAULT_TIMEZONE;
        $this->filters['interval_minutes'] ??= 30;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Evento')
                    ->options(Event::orderBy('title', 'asc')->pluck('title', 'id'))
                    ->placeholder('Selecciona un evento')
                    ->searchable()
                    ->live(),
                Select::make('timezone')
                    ->label('Zona horaria')
                    ->options($this->getTimezoneOptions())
                    ->searchable()
                    ->default(self::DEFAULT_TIMEZONE)
                    ->live(),
                Select::make('interval_minutes')
                    ->label('Intervalo de acceso')
                    ->options([
                        15 => 'Cada 15 minutos',
                        30 => 'Cada 30 minutos',
                        60 => 'Cada 60 minutos',
                    ])
                    ->default(30)
                    ->live(),
            ])
            ->columns([
                'md' => 3,
                'xl' => 3,
            ])
            ->statePath('filters');
    }

    protected function getDefaultEventId(): ?int
    {
        return Event::orderBy('title', 'asc')->value('id');
    }

    protected function getEventId(): ?int
    {
        $eventId = $this->filters['event_id'] ?? null;

        if (! $eventId) {
            return null;
        }

        return (int) $eventId;
    }

    protected function getTimezone(): string
    {
        $timezone = $this->filters['timezone'] ?? self::DEFAULT_TIMEZONE;

        return $timezone ?: self::DEFAULT_TIMEZONE;
    }

    protected function getIntervalMinutes(): int
    {
        $intervalMinutes = (int) ($this->filters['interval_minutes'] ?? 30);

        return $this->normalizeIntervalMinutes($intervalMinutes);
    }

    protected function getIntervalLabel(): string
    {
        $interval = $this->getIntervalMinutes();

        return match ($interval) {
            15 => 'Cada 15 min',
            30 => 'Cada 30 min',
            60 => 'Cada 60 min',
            default => 'Cada ' . $interval . ' min',
        };
    }

    protected function getTimezoneOptions(): array
    {
        return [
            self::DEFAULT_TIMEZONE => 'Cancun (America/Cancun)',
            'America/Mexico_City' => 'Ciudad de Mexico (America/Mexico_City)',
            'America/Bogota' => 'Bogota (America/Bogota)',
            'UTC' => 'UTC',
        ];
    }

    protected function normalizeIntervalMinutes(int $intervalMinutes): int
    {
        $allowed = [15, 30, 60];

        return in_array($intervalMinutes, $allowed, true) ? $intervalMinutes : 30;
    }

    protected function inTimezone(?Carbon $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        return $value->copy()->setTimezone($this->getTimezone());
    }

    protected function getSelectedEvent(): ?Event
    {
        $eventId = $this->getEventId();

        if (! $eventId) {
            return null;
        }
        
        return Event::with(['djs', 'rps'])->find($eventId);
    }

    protected function getQuery()
    {
        $query = GuestListEntry::query();
        
        $eventId = $this->getEventId();

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        return $query;
    }

    protected function getGuestListEntries()
    {
        return $this->getQuery()
            ->with(['customer', 'dj', 'rp', 'inviteLink.dj', 'inviteLink.rp'])
            ->join('customers', 'guest_list_entries.customer_id', '=', 'customers.id')
            ->orderBy('customers.name', 'asc')
            ->select('guest_list_entries.*')
            ->get()
            ->sortBy(function($entry) {
                return strtolower($entry->customer?->name ?? 'zzz');
            })
            ->values();
    }

    protected function getStatsByDj()
    {
        $query = $this->getQuery()
            ->whereNotNull('dj_id')
            ->select('dj_id', DB::raw('count(*) as total'))
            ->groupBy('dj_id')
            ->with('dj:id,name')
            ->orderByDesc('total')
            ->get();
        
        return $query->map(function ($item) {
            $baseQuery = $this->getQuery()->where('dj_id', $item->dj_id);
            
            return [
                'dj' => $item->dj?->name ?? 'Sin DJ',
                'total' => $item->total,
                'men' => (clone $baseQuery)->where('gender', 'masculino')->count(),
                'women' => (clone $baseQuery)->where('gender', 'femenino')->count(),
                'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
                'attended' => (clone $baseQuery)->where('status', 'attended')->count(),
            ];
        });
    }

    protected function getStatsByRp()
    {
        $query = $this->getQuery()
            ->whereNotNull('rp_id')
            ->select('rp_id', DB::raw('count(*) as total'))
            ->groupBy('rp_id')
            ->with('rp:id,name')
            ->orderByDesc('total')
            ->get();
        
        return $query->map(function ($item) {
            $baseQuery = $this->getQuery()->where('rp_id', $item->rp_id);
            
            return [
                'rp' => $item->rp?->name ?? 'Sin RP',
                'total' => $item->total,
                'men' => (clone $baseQuery)->where('gender', 'masculino')->count(),
                'women' => (clone $baseQuery)->where('gender', 'femenino')->count(),
                'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
                'attended' => (clone $baseQuery)->where('status', 'attended')->count(),
            ];
        });
    }

    protected function getStatsByStatus()
    {
        return $this->getQuery()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'label' => match($item->status) {
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'attended' => 'Asistió',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'No asistió',
                        default => ucfirst($item->status),
                    },
                    'total' => $item->total,
                ];
            });
    }

    protected function getStatsByGender()
    {
        return $this->getQuery()
            ->select('gender', DB::raw('count(*) as total'))
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'gender' => $item->gender,
                    'label' => match($item->gender) {
                        'masculino' => 'Hombres',
                        'femenino' => 'Mujeres',
                        'otro' => 'Otros',
                        default => ucfirst($item->gender),
                    },
                    'total' => $item->total,
                ];
            });
    }

    protected function getStatsByEvent()
    {
        return $this->getQuery()
            ->whereNotNull('event_id')
            ->select('event_id', DB::raw('count(*) as total'))
            ->groupBy('event_id')
            ->with('event:id,title,starts_at')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'event' => $item->event?->title ?? 'Sin evento',
                    'date' => $item->event?->starts_at?->format('d/m/Y'),
                    'total' => $item->total,
                ];
            });
    }

    protected function getTotalStats()
    {
        $query = $this->getQuery();
        
        return [
            'total' => $query->count(),
            'men' => (clone $query)->where('gender', 'masculino')->count(),
            'women' => (clone $query)->where('gender', 'femenino')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'attended' => (clone $query)->where('status', 'attended')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'no_show' => (clone $query)->where('status', 'no_show')->count(),
            'plus_ones' => (clone $query)->sum('plus_ones') ?? 0,
            'with_check_in' => (clone $query)->whereNotNull('check_in_at')->count(),
        ];
    }

    protected function getScanStats(): array
    {
        $query = GuestListScan::query();

        $eventId = $this->getEventId();

        if ($eventId) {
            $query->whereHas('guestListEntry', fn ($subQuery) => $subQuery->where('event_id', $eventId));
        }

        $total = (clone $query)->count();
        $rescanned = (clone $query)->whereIn('scan_status', self::RESCAN_STATUSES)->count();
        $unique = (clone $query)->where('scan_status', self::CHECKED_IN_STATUS)->count();

        return [
            'total' => $total,
            'unique' => $unique,
            'rescanned' => $rescanned,
        ];
    }

    protected function getScanQuery(): Builder
    {
        $query = GuestListScan::query();
        $eventId = $this->getEventId();

        if ($eventId) {
            $query->whereHas('guestListEntry', fn (Builder $subQuery) => $subQuery->where('event_id', $eventId));
        }

        return $query;
    }

    protected function getAccessStats(): array
    {
        $query = $this->getScanQuery();

        $total = (clone $query)->count();
        $checkIns = (clone $query)->where('scan_status', self::CHECKED_IN_STATUS)->count();
        $rescans = (clone $query)->whereIn('scan_status', self::RESCAN_STATUSES)->count();
        $uniqueGuests = (clone $query)
            ->where('scan_status', self::CHECKED_IN_STATUS)
            ->distinct('guest_list_entry_id')
            ->count('guest_list_entry_id');
        $rejected = (clone $query)->where('scan_status', 'rejected')->count();
        $read = (clone $query)->where('scan_status', 'read')->count();
        $limitReached = (clone $query)->where('scan_status', 'limit_reached')->count();
        $duplicate = (clone $query)->where('scan_status', 'duplicate')->count();

        $rescanRate = $total > 0 ? round(($rescans / $total) * 100, 1) : 0;
        $successRate = $total > 0 ? round(($checkIns / $total) * 100, 1) : 0;
        $rejectionRate = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;
        $scansPerGuest = $uniqueGuests > 0 ? round($total / $uniqueGuests, 2) : 0;
        $rescansPerGuest = $uniqueGuests > 0 ? round($rescans / $uniqueGuests, 2) : 0;

        return [
            'total' => $total,
            'check_ins' => $checkIns,
            'unique_guests' => $uniqueGuests,
            'rescans' => $rescans,
            'rejected' => $rejected,
            'read' => $read,
            'limit_reached' => $limitReached,
            'duplicate' => $duplicate,
            'rescan_rate' => $rescanRate,
            'success_rate' => $successRate,
            'rejection_rate' => $rejectionRate,
            'scans_per_guest' => $scansPerGuest,
            'rescans_per_guest' => $rescansPerGuest,
        ];
    }

    protected function getAccessTimingStats(): array
    {
        $event = $this->getSelectedEvent();
        $eventStart = $event?->starts_at;
        $checkInsQuery = $this->getScanQuery()->where('scan_status', self::CHECKED_IN_STATUS);

        $firstScan = (clone $checkInsQuery)->orderBy('scanned_at')->first(['scanned_at']);
        $lastScan = (clone $checkInsQuery)->orderByDesc('scanned_at')->first(['scanned_at']);
        $firstScanAt = $firstScan?->scanned_at;
        $lastScanAt = $lastScan?->scanned_at;

        $totalCheckIns = (clone $checkInsQuery)->count();

        $preStartCount = 0;
        $firstHourCount = 0;
        $preStartRate = 0;
        $firstHourRate = 0;
        $firstOffsetMinutes = null;

        if ($eventStart) {
            $preStartCount = (clone $checkInsQuery)->where('scanned_at', '<', $eventStart)->count();
            $firstHourCount = (clone $checkInsQuery)
                ->whereBetween('scanned_at', [$eventStart, $eventStart->copy()->addHour()])
                ->count();
            $preStartRate = $totalCheckIns > 0 ? round(($preStartCount / $totalCheckIns) * 100, 1) : 0;
            $firstHourRate = $totalCheckIns > 0 ? round(($firstHourCount / $totalCheckIns) * 100, 1) : 0;
            $firstOffsetMinutes = $firstScanAt ? $eventStart->diffInMinutes($firstScanAt, false) : null;
        }

        $doorDurationMinutes = ($firstScanAt && $lastScanAt)
            ? $firstScanAt->diffInMinutes($lastScanAt)
            : null;

        return [
            'event_start' => $this->inTimezone($eventStart),
            'first_scan_at' => $this->inTimezone($firstScanAt),
            'last_scan_at' => $this->inTimezone($lastScanAt),
            'first_offset_minutes' => $firstOffsetMinutes,
            'door_duration_minutes' => $doorDurationMinutes,
            'pre_start_count' => $preStartCount,
            'pre_start_rate' => $preStartRate,
            'first_hour_count' => $firstHourCount,
            'first_hour_rate' => $firstHourRate,
        ];
    }

    protected function getFrequencyStats(): array
    {
        $checkIns = $this->getScanQuery()
            ->where('scan_status', self::CHECKED_IN_STATUS)
            ->orderBy('scanned_at')
            ->get(['scanned_at']);

        if ($checkIns->isEmpty()) {
            return [
                'first_entry_at' => null,
                'last_entry_at' => null,
                'avg_per_hour' => 0,
                'avg_minutes_between' => null,
                'peak_interval' => null,
            ];
        }

        $first = $this->inTimezone($checkIns->first()->scanned_at);
        $last = $this->inTimezone($checkIns->last()->scanned_at);
        $spanMinutes = $first && $last ? max($first->diffInMinutes($last), 0) : 0;

        $avgPerHour = $spanMinutes > 0
            ? round($checkIns->count() / ($spanMinutes / 60), 1)
            : $checkIns->count();

        $avgMinutesBetween = null;
        if ($checkIns->count() > 1) {
            $totalDiff = 0;

            for ($i = 1; $i < $checkIns->count(); $i++) {
                $previous = $this->inTimezone($checkIns[$i - 1]->scanned_at);
                $current = $this->inTimezone($checkIns[$i]->scanned_at);

                if ($previous && $current) {
                    $totalDiff += $previous->diffInMinutes($current);
                }
            }

            $avgMinutesBetween = round($totalDiff / ($checkIns->count() - 1), 1);
        }

        $buckets = $this->getAccessBuckets();
        $peakInterval = empty($buckets)
            ? null
            : collect($buckets)->sortByDesc('checked_in')->first();

        return [
            'first_entry_at' => $first,
            'last_entry_at' => $last,
            'avg_per_hour' => $avgPerHour,
            'avg_minutes_between' => $avgMinutesBetween,
            'peak_interval' => $peakInterval,
        ];
    }

    protected function getAccessBuckets(): array
    {
        $intervalMinutes = $this->getIntervalMinutes();

        $scans = $this->getScanQuery()
            ->whereIn('scan_status', array_merge([self::CHECKED_IN_STATUS], self::RESCAN_STATUSES))
            ->orderBy('scanned_at')
            ->get(['scanned_at', 'scan_status']);

        return $this->buildIntervalBuckets($scans, $intervalMinutes);
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
                $bucketEnd = $bucketStart->copy()->addMinutes($intervalMinutes);
                $buckets[$bucketKey] = [
                    'label' => $bucketStart->format($labelFormat),
                    'range' => $bucketStart->format($labelFormat) . ' - ' . $bucketEnd->format($labelFormat),
                    'checked_in' => 0,
                    'rescans' => 0,
                    'total' => 0,
                ];
            }

            if ($scan->scan_status === self::CHECKED_IN_STATUS) {
                $buckets[$bucketKey]['checked_in']++;
            } else {
                $buckets[$bucketKey]['rescans']++;
            }

            $buckets[$bucketKey]['total']++;
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

    public function getViewData(): array
    {
        return [
            'event' => $this->getSelectedEvent(),
            'totalStats' => $this->getTotalStats(),
            'statsByDj' => $this->getStatsByDj(),
            'statsByRp' => $this->getStatsByRp(),
            'statsByStatus' => $this->getStatsByStatus(),
            'statsByGender' => $this->getStatsByGender(),
            'scanStats' => $this->getScanStats(),
            'accessStats' => $this->getAccessStats(),
            'accessFrequency' => $this->getFrequencyStats(),
            'accessTiming' => $this->getAccessTimingStats(),
            'accessBuckets' => $this->getAccessBuckets(),
            'accessIntervalLabel' => $this->getIntervalLabel(),
            'timezone' => $this->getTimezone(),
            'guestListEntries' => $this->getGuestListEntries(),
        ];
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => $this->getEventId() !== null)
                ->action(function () {
                    return $this->exportToExcel();
                }),
        ];
    }

    public function exportToExcel()
    {
        $eventId = $this->getEventId();

        if (! $eventId) {
            Notification::make()
                ->title('Error')
                ->body('Por favor selecciona un evento primero')
                ->danger()
                ->send();
            return;
        }

        $entries = $this->getGuestListEntries();
        $event = $this->getSelectedEvent();
        $timezone = $this->getTimezone();
        
        if (!$event) {
            Notification::make()
                ->title('Error')
                ->body('Evento no encontrado')
                ->danger()
                ->send();
            return;
        }
        
        $fileName = 'guest-list-' . Str::slug($event->title) . '-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($entries) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8 (Excel compatibility)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Nombre',
                'Email',
                'Instagram',
                'DJ',
                'RP',
                'Link de Invitación',
                'Género',
                'Acompañantes',
                'Estado',
                'Check-in',
                'Fecha de Registro'
            ]);
            
            // Data
            foreach ($entries as $entry) {
                fputcsv($file, [
                    $entry->customer?->name ?? 'N/A',
                    $entry->customer?->email ?? 'N/A',
                    $entry->customer?->instagram_handle ?? 'N/A',
                    $entry->dj?->name ?? 'N/A',
                    $entry->rp?->name ?? 'N/A',
                    $entry->inviteLink?->name ?? 'Manual',
                    match($entry->gender) {
                        'masculino' => 'Hombres',
                        'femenino' => 'Mujeres',
                        'otro' => 'Otros',
                        default => $entry->gender ?? 'N/A'
                    },
                    $entry->plus_ones ?? 0,
                    match($entry->status) {
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'attended' => 'Asistió',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'No asistió',
                        default => $entry->status
                    },
                    $entry->check_in_at?->copy()->setTimezone($timezone)->format('d/m/Y H:i') ?? 'N/A',
                    $entry->created_at?->copy()->setTimezone($timezone)->format('d/m/Y H:i') ?? 'N/A'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function sendQr(int $entryId): void
    {
        $entry = GuestListEntry::with(['customer', 'event'])->find($entryId);
        $eventId = $this->getEventId();

        if (! $entry || ($eventId && $entry->event_id !== $eventId)) {
            Notification::make()
                ->title('Registro no encontrado')
                ->body('No pudimos enviar el QR para este invitado.')
                ->danger()
                ->send();
            return;
        }

        if (! $entry->customer || ! $entry->customer->email) {
            Notification::make()
                ->title('Email faltante')
                ->body('El invitado no tiene un email registrado.')
                ->warning()
                ->send();
            return;
        }

        SendEventConfirmationJob::dispatchAfterResponse($entry);

        Notification::make()
            ->title('Email enviado')
            ->body('Se envió la confirmación con el QR al invitado.')
            ->success()
            ->send();
    }

}
