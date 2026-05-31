<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Fuentes con mejor calidad</x-slot>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Fuente</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Sesiones</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Leads</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Lead rate</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($sources as $source)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $source['source'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $source['channel'] }}</div>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format((int) $source['sessions']) }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format((int) $source['identified_leads']) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ number_format((float) $source['lead_rate'], 1) }}%</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">${{ number_format((float) $source['revenue'], 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">Sin fuentes atribuidas todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
