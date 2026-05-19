<x-filament-panels::page>
    @php
        $calendarInfo = $this->getCalendarInfo(app(\App\Services\GoogleCalendarService::class));
        $isGcalConnected = $calendarInfo['connected'];
        $upcomingSlots = $this->getUpcomingSlotCount();
        $slotsByTime = $this->getAvailableSlotsByTime();
        $lastGeneration = $this->getLastSlotGeneration();
        $expectedTimes = \App\Services\BookingAvailabilityRuleService::defaultSlots();
        $gcalConfigured = $this->getGoogleClientConfigured();
        $settings = \App\Models\SiteSetting::current();
        $selectedCalendarId = $settings?->google_calendar_id ?? 'primary';
    @endphp

    {{-- Google Calendar Integration Card --}}
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                    </svg>
                    Google Calendar
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Sincroniza disponibilidad y crea eventos automáticamente al confirmar una reserva.
                    Conecta una cuenta <strong>@lapsique</strong> de Google Workspace, elige un calendario dedicado y configura el email interno en el formulario de abajo.
                </p>
            </div>

            @if ($isGcalConnected)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    Conectado
                </span>
            @elseif (! empty($calendarInfo['needs_reconnect']))
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Reconectar
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-3 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-500/20 dark:bg-gray-800 dark:text-gray-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                    No conectado
                </span>
            @endif
        </div>

        @if (! $gcalConfigured)
        <div class="rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 p-4 text-sm space-y-2">
            <p class="font-medium text-amber-800 dark:text-amber-400">⚠️ Credenciales de Google no configuradas</p>
            <p class="text-amber-700 dark:text-amber-300">Agrega al archivo <code class="font-mono bg-amber-100 dark:bg-amber-500/20 px-1 rounded">.env</code>:</p>
            <pre class="font-mono text-xs bg-amber-100 dark:bg-amber-500/10 rounded p-3 text-amber-900 dark:text-amber-200 overflow-x-auto">GOOGLE_CLIENT_ID=tu-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu-client-secret</pre>
            <p class="text-amber-700 dark:text-amber-300">
                Crea tus credenciales en 
                <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="underline font-medium">Google Cloud Console</a>
                con el Scope <strong>Google Calendar API</strong> y la URI de callback:
            </p>
            <pre class="font-mono text-xs bg-amber-100 dark:bg-amber-500/10 rounded p-3 text-amber-900 dark:text-amber-200 overflow-x-auto">{{ route('google-calendar.oauth.callback') }}</pre>
        </div>
        @endif

        @if (! empty($calendarInfo['needs_reconnect']))
        <div class="rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 p-4 text-sm text-amber-800 dark:text-amber-300">
            <p class="font-medium">La conexión guardada ya no es válida</p>
            <p class="mt-1">{{ $calendarInfo['last_error_message'] ?? 'Vuelve a conectar Google Calendar.' }}</p>
        </div>
        @endif

        @if ($isGcalConnected)
        <div class="rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 p-4 space-y-2">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-green-600 dark:text-green-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ $calendarInfo['account_name'] ?? 'Cuenta conectada' }}</p>
                    <p class="text-xs text-green-700 dark:text-green-400">{{ $calendarInfo['account_email'] }}</p>
                </div>
            </div>

            @if ($calendarInfo['expires_at'])
            <p class="text-xs text-green-600 dark:text-green-500">Token válido hasta: {{ \Carbon\Carbon::parse($calendarInfo['expires_at'])->format('d/m/Y H:i') }}</p>
            @endif
        </div>

        {{-- Calendar selector --}}
        @if (! empty($this->calendarOptions))
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Calendario activo</label>
            <p class="text-xs text-gray-500 dark:text-gray-400">Los eventos de reservas se crearán en este calendario. También se lee la disponibilidad desde aquí.</p>
            <div class="flex gap-3">
                <select id="calendar-selector" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach ($this->calendarOptions as $calId => $calName)
                    <option value="{{ $calId }}" {{ $selectedCalendarId === $calId ? 'selected' : '' }}>{{ $calName }}</option>
                    @endforeach
                </select>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-100 transition"
                    onclick="saveCalendarSelection()">
                    Guardar calendario
                </button>
            </div>
        </div>
        @else
        <div class="text-sm text-gray-500">
            No se pudieron cargar los calendarios. Recarga la página o reconecta tu cuenta.
        </div>
        @endif

        <div class="pt-2">
            <form method="POST" action="{{ route('google-calendar.oauth.disconnect') }}" onsubmit="return confirm('¿Desconectar Google Calendar?')">
                @csrf
                <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:underline">
                    Desconectar Google Calendar
                </button>
            </form>
        </div>

        @else

        <div>
            @if ($gcalConfigured)
            <a href="{{ route('google-calendar.oauth.redirect') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Conectar con Google Calendar
            </a>
            @else
            <p class="text-sm text-gray-500">Configura las credenciales de Google antes de conectar.</p>
            @endif
        </div>
        @endif
    </div>

    {{-- Slot generation card --}}
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Generación de horarios</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Genera horarios automáticamente a partir de las 
                    <a href="{{ \App\Filament\Resources\BookingAvailabilityRules\BookingAvailabilityRuleResource::getUrl() }}" class="text-primary-600 hover:underline">reglas de disponibilidad</a>.
                    @if ($isGcalConnected) Verifica conflictos con Google Calendar. @endif
                </p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $upcomingSlots }}</p>
                <p class="text-xs text-gray-500">horarios activos</p>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($expectedTimes as $slot)
                @php $count = $slotsByTime[$slot['time_value']] ?? 0; @endphp
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $slot['time_label'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $count }} slots disponibles</p>
                </div>
            @endforeach
        </div>

        @if ($lastGeneration)
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Última generación: {{ \Carbon\Carbon::parse($lastGeneration['at'])->format('d/m/Y H:i') }}
                — creados {{ $lastGeneration['created'] ?? 0 }},
                omitidos {{ $lastGeneration['skipped'] ?? 0 }},
                bloqueados GCal {{ $lastGeneration['blocked_by_calendar'] ?? 0 }}.
            </p>
        @endif

        <div class="flex flex-wrap gap-3">
            <button
                wire:click="generateSlots"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-700 transition disabled:opacity-50">
                <svg wire:loading wire:target="generateSlots" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg wire:loading.remove wire:target="generateSlots" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Generar horarios faltantes
            </button>

            <button
                wire:click="clearAndRegenerate"
                wire:loading.attr="disabled"
                wire:confirm="¿Eliminar todos los horarios futuros sin reservas y regenerar? No se eliminarán horarios con reservas."
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition disabled:opacity-50">
                <svg wire:loading wire:target="clearAndRegenerate" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg wire:loading.remove wire:target="clearAndRegenerate" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Borrar y regenerar todo
            </button>
        </div>

        <div class="rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 p-3 text-xs text-blue-700 dark:text-blue-300 space-y-1">
            <p>💡 <strong>¿Cómo funciona?</strong></p>
            <ol class="list-decimal list-inside space-y-1 ml-2">
                <li>Crea reglas en <strong>Disponibilidad</strong> (días y horarios recurrentes).</li>
                <li>Haz clic en <strong>Generar horarios</strong> para crear los slots de los próximos {{ $settings?->bookingAvailabilityDays() ?? config('booking.availability_days', 11) }} días.</li>
                <li>Si tienes Google Calendar conectado, los horarios con conflictos se omiten automáticamente.</li>
                <li>Cuando alguien reserva y paga, se crea un evento en tu Google Calendar.</li>
            </ol>
        </div>
    </div>

    {{-- Settings form --}}
    <form wire:submit="save">
        {{ $this->schema }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Guardar configuración
            </x-filament::button>
        </div>
    </form>

    <script>
    function saveCalendarSelection() {
        const calendarId = document.getElementById('calendar-selector')?.value;
        if (!calendarId) return;

        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ calendar_id: calendarId, _method: 'POST' })
        });

        // Use Livewire to call saveCalendar
        @this.call('saveCalendar');
    }
    </script>
</x-filament-panels::page>
