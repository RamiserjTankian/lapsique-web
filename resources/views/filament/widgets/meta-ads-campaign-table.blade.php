<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">KPI por campaña</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Cruce de gasto Meta (utm_campaign = campaign_id) con leads y ventas del sitio — últimos {{ $periodDays }} días.
                </p>
            </div>
        </div>

        @if (! empty($loadError))
            <div class="mt-4 rounded-lg border border-danger-200 bg-danger-50 p-4 text-sm text-danger-800 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-300">
                No se pudo cargar la tabla: {{ $loadError }}
            </div>
        @endif

        <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Campaña</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Gasto</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Leads</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Ventas</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Revenue</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">CPL</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">CPA</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">ROAS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($campaigns as $row)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $row['campaign_name'] }}</div>
                                @if ($row['campaign_id'])
                                    <div class="text-xs text-gray-500">ID {{ $row['campaign_id'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">${{ number_format((float) $row['spend'], 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((int) $row['leads']) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((int) $row['sales_closed']) }}</td>
                            <td class="px-4 py-3 text-right">${{ number_format((float) $row['revenue'], 0) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">
                                {{ $row['cpl'] !== null ? '$'.number_format((float) $row['cpl'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">
                                {{ $row['cpa'] !== null ? '$'.number_format((float) $row['cpa'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                {{ $row['roas'] !== null ? number_format((float) $row['roas'], 2).'x' : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                Sin datos. Sincroniza insights de Meta o verifica UTMs en tus anuncios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
