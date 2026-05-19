<x-filament-panels::page>
    @if (session('mercadopago_success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('mercadopago_success') }}
        </div>
    @endif

    @if (session('mercadopago_error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('mercadopago_error') }}
        </div>
    @endif

    @if (! ($this->connection['storage_ready'] ?? false))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            La tabla `payment_gateway_connections` todavia no existe en esta base. La pantalla puede mostrar el estado actual por `.env`, pero OAuth persistente requiere ejecutar las migraciones nuevas.
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Estado</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">
                {{ ($this->connection['connected'] ?? false) ? 'Conectado' : 'Sin conexión' }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                {{ $this->connection['account_email'] ?? 'Sin cuenta enlazada.' }}
            </p>
            <p class="mt-2 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                {{ $this->connection['status_label'] ?? 'N/D' }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Cuenta</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">
                {{ $this->connection['account_name'] ?? 'Pendiente' }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                ID: {{ $this->connection['account_id'] ?? 'N/D' }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Expiración</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">
                {{ $this->connection['expires_at'] ? $this->connection['expires_at']->format('d M Y H:i') : 'Auto refresh' }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                Última sync: {{ $this->connection['last_synced_at'] ? $this->connection['last_synced_at']->format('d M Y H:i') : 'N/D' }}
            </p>
        </div>
    </div>

    @if (! empty($this->connection['last_error_message']))
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            {{ $this->connection['last_error_message'] }}
        </div>
    @endif

    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Flujo habilitado</h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Una vez conectada la cuenta, el sitio crea preferencias de Mercado Pago, procesa los webhooks y reconcilia pagos para liberar tickets y registrar ventas del evento.
        </p>

        @if ($this->connection['managed_by_env'] ?? false)
            <div class="mt-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
                Los cobros están operando con `MERCADOPAGO_ACCESS_TOKEN` en `.env`. Por eso puede cobrar aunque no exista una conexión OAuth guardada.
            </div>
        @endif

        <dl class="mt-6 space-y-4">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Redirect URI OAuth</dt>
                <dd class="mt-1 rounded-lg bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $this->connection['redirect_uri'] ?? 'N/D' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Webhook</dt>
                <dd class="mt-1 rounded-lg bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $this->connection['webhook_url'] ?? 'N/D' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Public Key</dt>
                <dd class="mt-1 rounded-lg bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $this->connection['public_key'] ?? 'No disponible' }}
                </dd>
            </div>
        </dl>
    </div>
</x-filament-panels::page>
