<x-filament-widgets::widget>
    <div class="space-y-6" @if($polling_interval) wire:poll.{{ $polling_interval }} @endif>
        <x-filament::section>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Usuarios en tiempo real</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Ventana activa de {{ $active_window_seconds }}s. Refresco automatico {{ $polling_interval }}.
                    </p>
                </div>

                <div class="text-right text-xs text-gray-500 dark:text-gray-400">
                    <p>Actualizado {{ $generated_at->timezone(config('analytics.reporting_timezone'))->format('d/m H:i:s') }}</p>
                    <p>Resumen reciente de {{ $recent_window_minutes }} minutos</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Activos</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-950 dark:text-white">{{ number_format($stats['active_sessions']) }}</p>
                    <p class="mt-1 text-sm text-emerald-800 dark:text-emerald-200">sesiones navegando ahora</p>
                </div>

                <div class="rounded-2xl border border-sky-200 bg-sky-50/70 p-4 dark:border-sky-500/20 dark:bg-sky-500/10">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">Visitantes</p>
                    <p class="mt-2 text-3xl font-semibold text-sky-950 dark:text-white">{{ number_format($stats['active_visitors']) }}</p>
                    <p class="mt-1 text-sm text-sky-800 dark:text-sky-200">usuarios unicos activos</p>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">Entradas</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-950 dark:text-white">{{ number_format($stats['recent_entries']) }}</p>
                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">sesiones nuevas recientes</p>
                </div>

                <div class="rounded-2xl border border-rose-200 bg-rose-50/70 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Salidas</p>
                    <p class="mt-2 text-3xl font-semibold text-rose-950 dark:text-white">{{ number_format($stats['recent_exits']) }}</p>
                    <p class="mt-1 text-sm text-rose-800 dark:text-rose-200">duracion media {{ $stats['avg_duration_human'] }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-[0.8fr,1.2fr]">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Paginas abiertas ahora</h4>
                        <span class="text-xs uppercase tracking-[0.2em] text-gray-500">Top actual</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($current_pages as $page)
                            <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                <p class="truncate font-medium text-gray-900 dark:text-white">{{ $page['path'] }}</p>
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                    {{ number_format($page['sessions']) }}
                                </span>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-white/10">
                                No hay paginas activas en este momento.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Sesiones activas</h4>
                        <span class="text-xs uppercase tracking-[0.2em] text-gray-500">Navegacion actual</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($active_sessions_list as $session)
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200">
                                                En linea
                                            </span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $session['source_label'] }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $session['device_type'] }} / {{ $session['browser'] }}</span>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-900 dark:text-white">
                                            <span class="font-medium">Actual:</span> {{ $session['current_path'] }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                            Ruta: {{ $session['journey_label'] !== '' ? $session['journey_label'] : $session['landing_path'] }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $session['location'] }}</p>
                                    </div>

                                    <div class="text-right text-xs text-gray-500 dark:text-gray-400">
                                        <p>Inicio {{ optional($session['started_at'])?->timezone(config('analytics.reporting_timezone'))->format('H:i:s') }}</p>
                                        <p>Ultimo ping {{ optional($session['last_seen_at'])?->diffForHumans() }}</p>
                                        <p>Duracion {{ $session['duration_human'] }}</p>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-sky-100 px-2 py-1 text-sky-800 dark:bg-sky-500/15 dark:text-sky-200">
                                        {{ number_format($session['pageviews_count']) }} paginas
                                    </span>
                                    <span class="rounded-full bg-amber-100 px-2 py-1 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200">
                                        {{ number_format($session['events_count']) }} eventos
                                    </span>
                                    @if($session['last_event_name'])
                                        <span class="rounded-full bg-gray-100 px-2 py-1 text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                            ultimo evento: {{ $session['last_event_name'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-white/10">
                                No hay sesiones activas en este momento.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Entradas recientes</h4>
                        <span class="text-xs uppercase tracking-[0.2em] text-gray-500">Quien acaba de llegar</span>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Hora</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Entrada</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ruta</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @forelse($recent_entries_list as $session)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ optional($session['started_at'])?->timezone(config('analytics.reporting_timezone'))->format('H:i:s') }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $session['landing_path'] }}</td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $session['journey_label'] !== '' ? $session['journey_label'] : $session['landing_path'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin entradas nuevas en la ventana reciente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Salidas recientes</h4>
                        <span class="text-xs uppercase tracking-[0.2em] text-gray-500">Sesiones finalizadas</span>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Salida</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ultima pagina</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Duracion</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @forelse($recent_exits_list as $session)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ optional($session['last_seen_at'])?->timezone(config('analytics.reporting_timezone'))->format('H:i:s') }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $session['current_path'] }}</td>
                                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ $session['duration_human'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin salidas detectadas en la ventana reciente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
