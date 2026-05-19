<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Origen y canal</h3>
                    <span class="text-xs uppercase tracking-[0.2em] text-gray-500">Top fuentes</span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Canal</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Origen</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Sesiones</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Unicos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse($sources as $source)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $source['label'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $source['top_source'] }}</td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format((int) $source['sessions']) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ number_format((int) $source['unique_visitors']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin datos de origen.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Ubicacion por IP</h3>
                    <span class="text-xs uppercase tracking-[0.2em] text-gray-500">Pais, estado y ciudad</span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Pais</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Estado</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ciudad</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Sesiones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse($locations as $location)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $location['country_name'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $location['region'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $location['city'] }}</td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format((int) $location['sessions']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Aun no hay geolocalizacion disponible.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Eventos activados</h3>
                    <span class="text-xs uppercase tracking-[0.2em] text-gray-500">Interacciones del sitio</span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Evento</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Categoria</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Activaciones</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Unicos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse($events as $event)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $event['name'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $event['category'] }}</td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format((int) $event['count']) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ number_format((int) $event['unique_visitors']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin eventos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Sesiones recientes</h3>
                    <span class="text-xs uppercase tracking-[0.2em] text-gray-500">IP y comportamiento</span>
                </div>

                <div class="space-y-3">
                    @forelse($sessions as $session)
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $session->source_label ?: 'direct' }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $session->landing_path ?: '/' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ implode(', ', array_filter([$session->city, $session->region ?: $session->region_code, $session->country_name ?: $session->country])) ?: 'Ubicacion desconocida' }}
                                    </p>
                                </div>
                                <div class="text-right text-xs text-gray-500 dark:text-gray-400">
                                    <p>{{ $session->created_at?->timezone(config('analytics.reporting_timezone'))->format('d/m H:i') }}</p>
                                    <p>{{ $session->ip_address ?: 'IP N/D' }}</p>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                <span class="rounded-full bg-sky-100 px-2 py-1 text-sky-800 dark:bg-sky-500/15 dark:text-sky-200">
                                    {{ number_format((int) $session->pageviews_count) }} visitas
                                </span>
                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200">
                                    {{ number_format((int) $session->events_count) }} eventos
                                </span>
                                <span class="rounded-full bg-amber-100 px-2 py-1 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200">
                                    {{ $session->device_type ?: 'device N/D' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-white/10">
                            No hay sesiones recientes para mostrar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
