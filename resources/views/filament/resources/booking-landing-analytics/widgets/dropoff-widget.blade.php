<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Origen de entrada</h3>
                    <span class="text-xs uppercase tracking-[0.2em] text-gray-500">Sesiones y conversión</span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Origen</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Canal</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Sesiones</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Confirmadas</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Conv.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse($sources as $source)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $source['source'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $source['channel'] }}</td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format((int) $source['sessions']) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format((int) $source['confirmed']) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ number_format((float) $source['conversion_rate'], 2) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Sin sesiones atribuidas todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Dónde se quedan</h3>
                    <span class="text-xs uppercase tracking-[0.2em] text-gray-500">Drop-off actual</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Etapa</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Sesiones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @forelse($dropoffs as $dropoff)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $dropoff['stage'] }}</td>
                                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format((int) $dropoff['sessions']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-6 text-center text-gray-500">Sin abandono registrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-3">
                        @forelse($recentSessions as $row)
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $row['source'] }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $row['session']->landing_path ?: '/sesion-de-contenido' }}</p>
                                    </div>
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-xs font-medium',
                                        'bg-gray-100 text-gray-700 dark:bg-gray-500/15 dark:text-gray-200' => $row['stage_color'] === 'gray',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200' => in_array($row['stage_color'], ['info', 'primary'], true),
                                        'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200' => $row['stage_color'] === 'warning',
                                        'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-200' => $row['stage_color'] === 'danger',
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' => $row['stage_color'] === 'success',
                                    ])>{{ $row['stage'] }}</span>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $row['session']->device_type ?: 'device N/D' }}</span>
                                    <span>{{ $row['session']->referrer_domain ?: 'sin referrer' }}</span>
                                    <span>{{ $row['duration_seconds'] }}s</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-white/10">
                                No hay sesiones recientes para esta landing.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
