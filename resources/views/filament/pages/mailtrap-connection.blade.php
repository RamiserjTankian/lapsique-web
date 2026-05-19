<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Estado</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">
                {{ ($this->summary['can_send'] ?? false) ? 'Operativo' : 'Incompleto' }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                Mailer activo: {{ $this->summary['mailer'] ?? 'N/D' }}
            </p>
            <p class="mt-2 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                {{ $this->summary['mode_label'] ?? 'N/D' }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Remitente</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">
                {{ $this->summary['from_name'] ?? 'N/D' }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                {{ $this->summary['from_address'] ?? 'N/D' }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Cuenta</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">
                {{ $this->summary['account_id'] ?: 'N/D' }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                Webhook firmado: {{ ($this->summary['webhook_ready'] ?? false) ? 'Sí' : 'No' }}
            </p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Configuración activa</h3>

        <dl class="mt-6 space-y-4">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Webhook receptor</dt>
                <dd class="mt-1 rounded-lg bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $this->summary['webhook_url'] ?? 'N/D' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Secret del webhook</dt>
                <dd class="mt-1 rounded-lg bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $this->summary['webhook_secret_masked'] ?? 'No configurado' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">API Token</dt>
                <dd class="mt-1 rounded-lg bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $this->summary['api_token_masked'] ?? 'No configurado' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Events Endpoint</dt>
                <dd class="mt-1 rounded-lg bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $this->summary['events_endpoint'] ?: 'No configurado' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">SMTP</dt>
                <dd class="mt-1 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $this->summary['smtp_host'] ?: 'N/D' }}:{{ $this->summary['smtp_port'] ?: 'N/D' }}
                    · {{ ($this->summary['smtp_ready'] ?? false) ? 'Completo' : 'Incompleto' }}
                </dd>
            </div>
        </dl>

        @if (($this->summary['mode'] ?? null) === 'api')
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                Los envíos transaccionales del proyecto están saliendo por Mailtrap API.
            </div>
        @elseif (($this->summary['mode'] ?? null) === 'smtp')
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                La app está apuntando a Mailtrap por SMTP. Si quieres usar la API del proyecto, mantén configurado `MAILTRAP_API_TOKEN`.
            </div>
        @else
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Mailtrap aún no está completo para envío. Falta token API o credenciales SMTP utilizables.
            </div>
        @endif
    </div>
</x-filament-panels::page>
