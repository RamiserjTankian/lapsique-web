<x-filament-panels::page>
    @php
        $event = $event ?? $this->getSelectedEvent();
        $totalStats = $totalStats ?? $this->getTotalStats();
        $entries = $guestListEntries ?? $this->getGuestListEntries();
        $statsByStatus = $statsByStatus ?? $this->getStatsByStatus();
        $statsByGender = $statsByGender ?? $this->getStatsByGender();
        $scanStats = $scanStats ?? $this->getScanStats();
        $accessStats = $accessStats ?? $this->getAccessStats();
        $accessFrequency = $accessFrequency ?? $this->getFrequencyStats();
        $accessTiming = $accessTiming ?? $this->getAccessTimingStats();
        $accessBuckets = $accessBuckets ?? $this->getAccessBuckets();
        $accessIntervalLabel = $accessIntervalLabel ?? $this->getIntervalLabel();
        $timezone = $timezone ?? $this->getTimezone();
        $eventId = $eventId ?? $this->getEventId();
        $intervalMinutes = $intervalMinutes ?? $this->getIntervalMinutes();

        $totalCount = $totalStats['total'] ?? 0;
        $confirmedRate = $totalCount > 0 ? round(($totalStats['confirmed'] / $totalCount) * 100, 1) : 0;
        $attendanceRate = $totalCount > 0 ? round(($totalStats['attended'] / $totalCount) * 100, 1) : 0;
        $pendingRate = $totalCount > 0 ? round(($totalStats['pending'] / $totalCount) * 100, 1) : 0;
        $menRate = $totalCount > 0 ? round(($totalStats['men'] / $totalCount) * 100, 1) : 0;
        $womenRate = $totalCount > 0 ? round(($totalStats['women'] / $totalCount) * 100, 1) : 0;
        $confirmedCount = $totalStats['confirmed'] ?? 0;
        $attendedCount = $totalStats['attended'] ?? 0;
        $noShowCount = $totalStats['no_show'] ?? 0;
        $cancelledCount = $totalStats['cancelled'] ?? 0;
        $checkInRate = $totalCount > 0 ? round(($totalStats['with_check_in'] / $totalCount) * 100, 1) : 0;
        $confirmToAttendRate = $confirmedCount > 0 ? round(($attendedCount / $confirmedCount) * 100, 1) : 0;
        $noShowRate = $confirmedCount > 0 ? round(($noShowCount / $confirmedCount) * 100, 1) : 0;
        $cancelRate = $confirmedCount > 0 ? round(($cancelledCount / $confirmedCount) * 100, 1) : 0;
        $scanTotal = $scanStats['total'] ?? 0;
        $scanRescanned = $scanStats['rescanned'] ?? 0;
        $scanRescanRate = $scanTotal > 0 ? round(($scanRescanned / $scanTotal) * 100, 1) : 0;
        $accessRescanRate = $accessStats['rescan_rate'] ?? 0;
        $accessSuccessRate = $accessStats['success_rate'] ?? 0;
        $accessRejectionRate = $accessStats['rejection_rate'] ?? 0;
        $accessScansPerGuest = $accessStats['scans_per_guest'] ?? 0;
        $accessRescansPerGuest = $accessStats['rescans_per_guest'] ?? 0;
        $avgMinutesBetween = $accessFrequency['avg_minutes_between'] ?? null;
        $peakInterval = $accessFrequency['peak_interval']['range'] ?? null;
        $doorDurationMinutes = $accessTiming['door_duration_minutes'] ?? null;
        $firstOffsetMinutes = $accessTiming['first_offset_minutes'] ?? null;
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Filtros de evento
            </x-slot>

            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px]">
                <div class="rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Buscar evento</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Selecciona el evento, la zona horaria y el intervalo para ver accesos, registros y frecuencia en tiempo real.
                    </p>
                    <div class="mt-4">
                        {{ $this->form }}
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200/70 bg-gradient-to-br from-gray-50 via-white to-gray-100 p-4 shadow-sm dark:border-white/10 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                        <span>Totales</span>
                        <span>{{ $event ? 'Evento activo' : 'Sin evento' }}</span>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Zona horaria: {{ $timezone }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Intervalo accesos: {{ $accessIntervalLabel }}
                    </div>
                    <div class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">
                        {{ number_format($totalStats['total'] ?? 0) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        + {{ number_format($totalStats['plus_ones'] ?? 0) }} acompañantes
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Confirmados</span>
                            <span>{{ $confirmedRate }}%</span>
                        </div>
                        <div class="mt-2 h-2 w-full rounded-full bg-gray-200/70 dark:bg-white/10">
                            <div class="h-2 rounded-full bg-success-500" style="width: {{ $confirmedRate }}%"></div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Asistieron {{ $attendanceRate }}%</span>
                        <span>Pendientes {{ $pendingRate }}%</span>
                    </div>
                </div>
            </div>
        </x-filament::section>

        @if($event)
            <x-filament::section>
                <x-slot name="heading">
                    Información del Evento
                </x-slot>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(0,0.9fr)]">
                    <div class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Evento</p>
                                <h2 class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $event->title }}</h2>
                                @if($event->headline)
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $event->headline }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-2 rounded-full bg-success-50 px-3 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-300">
                                    <span class="h-2 w-2 rounded-full bg-success-500"></span>
                                    Activo
                                </span>
                                @if($event->starts_at)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-300">
                                        {{ $event->starts_at->copy()->setTimezone($timezone)->format('d M Y - H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($event->description)
                            <p class="mt-4 text-sm leading-relaxed text-gray-600 dark:text-gray-300 whitespace-pre-wrap">
                                {{ $event->description }}
                            </p>
                        @endif

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            @if($event->starts_at)
                                <div class="rounded-xl border border-gray-200/70 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Fecha y hora</p>
                                    <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $event->starts_at->copy()->setTimezone($timezone)->format('d/m/Y H:i') }}</p>
                                </div>
                            @endif

                            @if($event->venue)
                                <div class="rounded-xl border border-gray-200/70 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Venue</p>
                                    <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $event->venue }}</p>
                                </div>
                            @endif

                            @if($event->city)
                                <div class="rounded-xl border border-gray-200/70 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Ciudad</p>
                                    <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $event->city }}</p>
                                </div>
                            @endif

                            <div class="rounded-xl border border-gray-200/70 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Registros</p>
                                <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($totalStats['total'] ?? 0) }} invitados</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">+ {{ number_format($totalStats['plus_ones'] ?? 0) }} acompañantes</p>
                            </div>
                        </div>

                        @if($event->ticket_url)
                            <div class="mt-5 flex flex-wrap items-center gap-3">
                                <a href="{{ $event->ticket_url }}" target="_blank"
                                   class="inline-flex items-center gap-2 rounded-full bg-primary-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-primary-500">
                                    Ver tickets
                                </a>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $event->ticket_url }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Indicadores Guest List</p>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Registros</p>
                                    <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($totalStats['total'] ?? 0) }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">+ {{ number_format($totalStats['plus_ones'] ?? 0) }} acomp.</p>
                                </div>
                                <div class="rounded-xl bg-warning-50 p-3 dark:bg-warning-500/10">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-warning-600 dark:text-warning-300">Confirmados</p>
                                    <p class="mt-2 text-xl font-semibold text-warning-950 dark:text-warning-100">{{ number_format($confirmedCount) }}</p>
                                    <p class="mt-1 text-xs text-warning-600 dark:text-warning-300">{{ $confirmedRate }}% del total</p>
                                </div>
                                <div class="rounded-xl bg-primary-50 p-3 dark:bg-primary-500/10">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary-600 dark:text-primary-300">Asistieron</p>
                                    <p class="mt-2 text-xl font-semibold text-primary-950 dark:text-primary-100">{{ number_format($attendedCount) }}</p>
                                    <p class="mt-1 text-xs text-primary-600 dark:text-primary-300">{{ $confirmToAttendRate }}% de confirmados</p>
                                </div>
                                <div class="rounded-xl bg-danger-50 p-3 dark:bg-danger-500/10">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-danger-600 dark:text-danger-300">No show</p>
                                    <p class="mt-2 text-xl font-semibold text-danger-950 dark:text-danger-100">{{ number_format($noShowCount) }}</p>
                                    <p class="mt-1 text-xs text-danger-600 dark:text-danger-300">{{ $noShowRate }}% de confirmados</p>
                                </div>
                                <div class="rounded-xl bg-info-50 p-3 dark:bg-info-500/10">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-info-600 dark:text-info-300">Check-in</p>
                                    <p class="mt-2 text-xl font-semibold text-info-950 dark:text-info-100">{{ number_format($totalStats['with_check_in'] ?? 0) }}</p>
                                    <p class="mt-1 text-xs text-info-600 dark:text-info-300">{{ $checkInRate }}% del total</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Cancelados</p>
                                    <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($cancelledCount) }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $cancelRate }}% de confirmados</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Indicadores QR</p>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-primary-50 p-3 dark:bg-primary-500/10">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary-600 dark:text-primary-300">Escaneos</p>
                                    <p class="mt-2 text-xl font-semibold text-primary-950 dark:text-primary-100">{{ number_format($accessStats['total'] ?? 0) }}</p>
                                    <p class="mt-1 text-xs text-primary-600 dark:text-primary-300">{{ $accessScansPerGuest }} por invitado</p>
                                </div>
                                <div class="rounded-xl bg-success-50 p-3 dark:bg-success-500/10">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-success-600 dark:text-success-300">Entradas validas</p>
                                    <p class="mt-2 text-xl font-semibold text-success-950 dark:text-success-100">{{ number_format($accessStats['check_ins'] ?? 0) }}</p>
                                    <p class="mt-1 text-xs text-success-600 dark:text-success-300">{{ $accessSuccessRate }}% de exito</p>
                                </div>
                                <div class="rounded-xl bg-warning-50 p-3 dark:bg-warning-500/10">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-warning-600 dark:text-warning-300">Reescaneos</p>
                                    <p class="mt-2 text-xl font-semibold text-warning-950 dark:text-warning-100">{{ number_format($accessStats['rescans'] ?? 0) }}</p>
                                    <p class="mt-1 text-xs text-warning-600 dark:text-warning-300">{{ $accessRescanRate }}% del total</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Reescaneos por invitado</p>
                                    <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">{{ $accessRescansPerGuest }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Promedio</p>
                                </div>
                                <div class="rounded-xl bg-danger-50 p-3 dark:bg-danger-500/10">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-danger-600 dark:text-danger-300">Rechazados</p>
                                    <p class="mt-2 text-xl font-semibold text-danger-950 dark:text-danger-100">{{ number_format($accessStats['rejected'] ?? 0) }}</p>
                                    <p class="mt-1 text-xs text-danger-600 dark:text-danger-300">{{ $accessRejectionRate }}% del total</p>
                                </div>
                                <div class="rounded-xl bg-info-50 p-3 dark:bg-info-500/10">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-info-600 dark:text-info-300">Lecturas</p>
                                    <p class="mt-2 text-xl font-semibold text-info-950 dark:text-info-100">{{ number_format($accessStats['read'] ?? 0) }}</p>
                                    <p class="mt-1 text-xs text-info-600 dark:text-info-300">Sin confirmar</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Timing de accesos</p>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ $accessTiming['event_start'] ? $accessTiming['event_start']->format('d/m H:i') : '—' }}</p>
                                    <p>Inicio evento</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ $accessTiming['first_scan_at'] ? $accessTiming['first_scan_at']->format('d/m H:i') : '—' }}</p>
                                    <p>Primer ingreso</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="font-semibold text-gray-950 dark:text-white">
                                        @if($firstOffsetMinutes !== null)
                                            {{ $firstOffsetMinutes >= 0 ? '+' . $firstOffsetMinutes : $firstOffsetMinutes }} min
                                        @else
                                            —
                                        @endif
                                    </p>
                                    <p>Diferencia inicio</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ number_format($accessTiming['pre_start_count'] ?? 0) }}</p>
                                    <p>Antes del inicio ({{ $accessTiming['pre_start_rate'] ?? 0 }}%)</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ number_format($accessTiming['first_hour_count'] ?? 0) }}</p>
                                    <p>En la primera hora ({{ $accessTiming['first_hour_rate'] ?? 0 }}%)</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ $avgMinutesBetween !== null ? number_format($avgMinutesBetween, 1) . ' min' : '—' }}</p>
                                    <p>Promedio entre entradas</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ $doorDurationMinutes !== null ? $doorDurationMinutes . ' min' : '—' }}</p>
                                    <p>Duracion de puerta</p>
                                </div>
                                <div class="rounded-xl bg-primary-50 p-3 dark:bg-primary-500/10">
                                    <p class="font-semibold text-primary-950 dark:text-primary-100">{{ $peakInterval ?? '—' }}</p>
                                    <p>Hora pico</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ $accessTiming['last_scan_at'] ? $accessTiming['last_scan_at']->format('d/m H:i') : '—' }}</p>
                                    <p>Ultimo ingreso</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Distribución
                </x-slot>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Por estado</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Confirmaciones y asistencia</p>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Total {{ number_format($totalCount) }}</span>
                        </div>

                        <div class="mt-4 space-y-4">
                            @forelse($statsByStatus as $statusStat)
                                @php
                                    $statusPercent = $totalCount > 0 ? round(($statusStat['total'] / $totalCount) * 100) : 0;
                                    $statusBarClass = match ($statusStat['status']) {
                                        'confirmed' => 'bg-success-500',
                                        'attended' => 'bg-primary-500',
                                        'pending' => 'bg-warning-500',
                                        'cancelled', 'no_show' => 'bg-danger-500',
                                        default => 'bg-gray-400',
                                    };
                                    $statusBadgeClass = match ($statusStat['status']) {
                                        'confirmed' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
                                        'attended' => 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300',
                                        'pending' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
                                        'cancelled', 'no_show' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300',
                                        default => 'bg-gray-50 text-gray-700 dark:bg-white/10 dark:text-gray-300',
                                    };
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClass }}">
                                            {{ $statusStat['label'] }}
                                        </span>
                                        <span>{{ number_format($statusStat['total']) }} · {{ $statusPercent }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 w-full rounded-full bg-gray-200/70 dark:bg-white/10">
                                        <div class="h-2 rounded-full {{ $statusBarClass }}" style="width: {{ $statusPercent }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay datos de estado disponibles.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Por género</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Distribución general</p>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Total {{ number_format($totalCount) }}</span>
                        </div>

                        <div class="mt-4 space-y-4">
                            @forelse($statsByGender as $genderStat)
                                @php
                                    $genderPercent = $totalCount > 0 ? round(($genderStat['total'] / $totalCount) * 100) : 0;
                                    $genderBarClass = match ($genderStat['gender']) {
                                        'masculino' => 'bg-info-500',
                                        'femenino' => 'bg-success-500',
                                        'otro' => 'bg-warning-500',
                                        default => 'bg-gray-400',
                                    };
                                    $genderBadgeClass = match ($genderStat['gender']) {
                                        'masculino' => 'bg-info-50 text-info-700 dark:bg-info-500/10 dark:text-info-300',
                                        'femenino' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
                                        'otro' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
                                        default => 'bg-gray-50 text-gray-700 dark:bg-white/10 dark:text-gray-300',
                                    };
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $genderBadgeClass }}">
                                            {{ $genderStat['label'] }}
                                        </span>
                                        <span>{{ number_format($genderStat['total']) }} · {{ $genderPercent }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 w-full rounded-full bg-gray-200/70 dark:bg-white/10">
                                        <div class="h-2 rounded-full {{ $genderBarClass }}" style="width: {{ $genderPercent }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay datos de género disponibles.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Graficas de registros
                </x-slot>

                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @livewire(\App\Filament\Widgets\GuestListGenderChartWidget::class, [], key('guestlist-gender-chart'))
                    @livewire(\App\Filament\Widgets\GuestListDjChartWidget::class, [], key('guestlist-dj-chart'))
                    @livewire(\App\Filament\Widgets\GuestListRpChartWidget::class, [], key('guestlist-rp-chart'))
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Accesos por QR
                </x-slot>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    @livewire(
                        \App\Filament\Widgets\GuestListAccessIntervalChartWidget::class,
                        ['eventId' => $eventId, 'intervalMinutes' => $intervalMinutes, 'timezone' => $timezone],
                        key('guestlist-access-interval-' . ($eventId ?? 'all') . '-' . $intervalMinutes . '-' . $timezone)
                    )
                    @livewire(
                        \App\Filament\Widgets\GuestListScanStatusChartWidget::class,
                        ['eventId' => $eventId],
                        key('guestlist-scan-status-' . ($eventId ?? 'all'))
                    )
                </div>

                <div class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                        <span>Personas por intervalo</span>
                        <span>{{ $accessIntervalLabel }}</span>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase tracking-[0.15em] text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-3 py-2">Intervalo</th>
                                    <th class="px-3 py-2">Entradas</th>
                                    <th class="px-3 py-2">Reescaneos</th>
                                    <th class="px-3 py-2">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700 dark:divide-white/10 dark:text-gray-200">
                                @forelse($accessBuckets as $bucket)
                                    <tr>
                                        <td class="px-3 py-2 font-medium">{{ $bucket['range'] }}</td>
                                        <td class="px-3 py-2">{{ number_format($bucket['checked_in']) }}</td>
                                        <td class="px-3 py-2">{{ number_format($bucket['rescans']) }}</td>
                                        <td class="px-3 py-2">{{ number_format($bucket['total']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Sin datos de accesos para este evento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Lista de Invitados ({{ $entries->count() }})
                </x-slot>

                <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>Ordenado por nombre de invitado.</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">Pendiente</span>
                        <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-300">Confirmado</span>
                        <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">Asistió</span>
                        <span class="inline-flex items-center rounded-full bg-danger-50 px-2.5 py-1 text-xs font-semibold text-danger-700 dark:bg-danger-500/10 dark:text-danger-300">Cancelado/No show</span>
                    </div>
                </div>

                @if($entries->count() > 0)
                    <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200/70 shadow-sm dark:border-white/10">
                        <div class="max-h-[70vh] overflow-auto">
                            <table class="min-w-full divide-y divide-gray-200/70 dark:divide-white/10">
                                <thead class="sticky top-0 z-10 bg-gray-50/95 text-xs uppercase tracking-wide text-gray-600 backdrop-blur dark:bg-gray-800/95 dark:text-gray-300">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">Nombre</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">Email</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">Instagram</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">DJ</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">RP</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">Link</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">Género</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">Acomp.</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">Estado</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">Check-in</th>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200/70 bg-white text-sm dark:divide-white/10 dark:bg-gray-900">
                                    @foreach($entries as $entry)
                                        @php
                                            $statusLabels = [
                                                'pending' => 'Pendiente',
                                                'confirmed' => 'Confirmado',
                                                'attended' => 'Asistió',
                                                'cancelled' => 'Cancelado',
                                                'no_show' => 'No asistió',
                                            ];
                                            $label = $statusLabels[$entry->status] ?? ucfirst($entry->status);
                                            $statusClass = match ($entry->status) {
                                                'pending' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
                                                'confirmed' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
                                                'attended' => 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300',
                                                'cancelled', 'no_show' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300',
                                                default => 'bg-gray-50 text-gray-700 dark:bg-white/10 dark:text-gray-300',
                                            };
                                            $genderLabel = match ($entry->gender) {
                                                'masculino' => 'Hombre',
                                                'femenino' => 'Mujer',
                                                'otro' => 'Otro',
                                                default => '—',
                                            };
                                            $genderClass = match ($entry->gender) {
                                                'masculino' => 'bg-info-50 text-info-700 dark:bg-info-500/10 dark:text-info-300',
                                                'femenino' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
                                                'otro' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
                                                default => 'bg-gray-50 text-gray-700 dark:bg-white/10 dark:text-gray-300',
                                            };
                                        @endphp
                                        <tr class="transition hover:bg-gray-50/80 dark:hover:bg-white/5">
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-300">
                                                        {{ strtoupper(substr($entry->customer?->name ?? 'N', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $entry->customer?->name ?? 'N/A' }}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $entry->created_at?->copy()->setTimezone($timezone)->format('d/m/Y') }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                                @if($entry->customer?->email)
                                                    <a href="mailto:{{ $entry->customer->email }}" class="font-medium text-primary-600 hover:underline dark:text-primary-300">
                                                        {{ $entry->customer->email }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                                @if($entry->customer?->instagram_handle)
                                                    <a href="https://instagram.com/{{ ltrim($entry->customer->instagram_handle, '@') }}" target="_blank"
                                                       class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/20">
                                                        {{ $entry->customer->instagram_handle }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                                @if($entry->dj)
                                                    <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                                        {{ $entry->dj->name }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                                @if($entry->rp)
                                                    <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-300">
                                                        {{ $entry->rp->name }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                                @if($entry->inviteLink)
                                                    <span class="inline-flex items-center rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                                                        {{ $entry->inviteLink->name ?? 'Link General' }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">Manual</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $genderClass }}">
                                                    {{ $genderLabel }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                                @if(($entry->plus_ones ?? 0) > 0)
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-300">
                                                        +{{ $entry->plus_ones }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">0</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                                    {{ $label }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                                @if($entry->check_in_at)
                                                    <div>
                                                        <p class="font-semibold text-gray-900 dark:text-white">Check-in</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $entry->check_in_at->copy()->setTimezone($timezone)->format('d/m/Y H:i') }}</p>
                                                    </div>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">Sin check-in</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <x-filament::button
                                                    type="button"
                                                    size="xs"
                                                    color="info"
                                                    wire:click="sendQr({{ $entry->id }})"
                                                    wire:loading.attr="disabled"
                                                >
                                                    Enviar QR
                                                </x-filament::button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-gray-300 p-8 text-center dark:border-white/20">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">No hay invitados registrados para este evento.</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Comparte el link de invitacion para iniciar los registros.</p>
                    </div>
                @endif
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="rounded-2xl border border-dashed border-gray-300 p-8 text-center dark:border-white/20">
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Selecciona un evento para ver las estadísticas.</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Los gráficos y métricas se actualizarán automáticamente.</p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
