<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid gap-6 xl:grid-cols-[1.15fr,1fr]">
            <div class="space-y-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Embudo del evento</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Seguimiento desde las entradas al evento hasta los clientes que terminan pagando.
                    </p>
                </div>

                @if (count($funnelRows) > 0)
                    <div class="space-y-3">
                        @foreach ($funnelRows as $row)
                            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $row['label'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ number_format($row['from_previous'], 1) }}% vs paso anterior
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-semibold text-gray-950 dark:text-white">{{ number_format($row['count']) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ number_format($row['from_entry'], 1) }}% del total
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 h-2.5 rounded-full bg-gray-100 dark:bg-white/10">
                                    <div
                                        class="h-2.5 rounded-full bg-primary-500"
                                        style="width: {{ min(100, max(4, $row['from_entry'])) }}%;"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                        Aún no hay suficiente tracking para construir el embudo de este evento.
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Perfil de quienes entran</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Desglose rápido de los clientes que llegan a la landing del evento.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <p class="text-sm font-medium text-gray-950 dark:text-white">Dispositivo</p>
                        <div class="mt-3 space-y-2">
                            @forelse ($deviceRows as $row)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-300">{{ $row['device'] }}</span>
                                    <span class="font-medium text-gray-950 dark:text-white">{{ number_format($row['visitors']) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sin datos.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <p class="text-sm font-medium text-gray-950 dark:text-white">Referrer</p>
                        <div class="mt-3 space-y-2">
                            @forelse ($referrerRows as $row)
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="truncate text-gray-600 dark:text-gray-300">{{ $row['referrer'] }}</span>
                                    <span class="font-medium text-gray-950 dark:text-white">{{ number_format($row['visitors']) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sin datos.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Desglose de clientes que entran</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Fuentes que más tráfico traen al evento y qué tan bien convierten hasta pago.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="py-3 pr-4 font-medium">Fuente</th>
                            <th class="py-3 pr-4 font-medium">Entran</th>
                            <th class="py-3 pr-4 font-medium">Tickets</th>
                            <th class="py-3 pr-4 font-medium">Carrito</th>
                            <th class="py-3 pr-4 font-medium">Checkout</th>
                            <th class="py-3 pr-4 font-medium">Pagan</th>
                            <th class="py-3 font-medium">Conversión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($sourceRows as $row)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-gray-950 dark:text-white">{{ $row['source'] }}</td>
                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ number_format($row['visitors']) }}</td>
                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ number_format($row['tickets']) }}</td>
                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ number_format($row['cart']) }}</td>
                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ number_format($row['checkout']) }}</td>
                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ number_format($row['paid']) }}</td>
                                <td class="py-3 font-semibold text-gray-950 dark:text-white">{{ number_format($row['conversion'], 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 text-sm text-gray-500 dark:text-gray-400">
                                    Todavía no hay datos suficientes para desglosar las entradas por fuente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
