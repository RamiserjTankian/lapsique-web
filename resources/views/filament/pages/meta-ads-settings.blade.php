@php
    use App\Support\Meta;

    $client = app(\App\Services\Meta\MetaMarketingApiClient::class);
    $marketingConfigured = $client->isConfigured();
    $capiEnabled = Meta::capiEnabled();
    $pixelEnabled = Meta::pixelEnabled();
    $pixelId = Meta::pixelId();
    $pixelIdSource = \App\Models\SiteSetting::metaPixelId()
        ? 'Base de datos (Configuración del sitio)'
        : (config('meta.pixel.id') ? '.env (META_PIXEL_ID)' : 'No configurado');
    $adAccountId = config('meta.marketing_api.ad_account_id');
    $apiVersion = config('meta.marketing_api.api_version', 'v21.0');
    $utmTemplate = config('meta.attribution.utm_template');
    $lastSyncedAt = \Illuminate\Support\Facades\Schema::hasTable('meta_campaign_daily_insights')
        ? \App\Models\MetaCampaignDailyInsight::query()->max('synced_at')
        : null;
@endphp

<x-filament-panels::page>
    <div class="grid gap-6">
        <x-filament::section>
            <x-slot name="heading">Estado de la integración</x-slot>

            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Pixel (browser)</dt>
                    <dd class="mt-1 text-gray-950 dark:text-white">
                        @if ($pixelEnabled)
                            <span class="text-success-600">Activo — {{ $pixelId }} ({{ $pixelIdSource }})</span>
                        @else
                            <span class="text-warning-600">Inactivo — META_PIXEL_ENABLED y pixel ID en sitio o .env</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Marketing API (gastos)</dt>
                    <dd class="mt-1 text-gray-950 dark:text-white">
                        @if ($marketingConfigured)
                            <span class="text-success-600">Configurada</span>
                        @else
                            <span class="text-danger-600">Pendiente — revisa META_ADS_ENABLED, META_ACCESS_TOKEN y META_AD_ACCOUNT_ID</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Conversions API (server-side)</dt>
                    <dd class="mt-1 text-gray-950 dark:text-white">
                        @if ($capiEnabled)
                            <span class="text-success-600">Activa (complementa el pixel {{ $pixelId }})</span>
                        @else
                            <span class="text-warning-600">Inactiva — META_CAPI_ENABLED=true, META_PIXEL_ID y META_ACCESS_TOKEN</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Cuenta publicitaria</dt>
                    <dd class="mt-1 font-mono text-gray-950 dark:text-white">{{ $adAccountId ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Última sincronización de insights</dt>
                    <dd class="mt-1 text-gray-950 dark:text-white">
                        {{ $lastSyncedAt ? \Illuminate\Support\Carbon::parse($lastSyncedAt)->timezone(config('app.timezone'))->format('d/m/Y H:i') : 'Nunca' }}
                    </dd>
                </div>
            </dl>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">UTMs en Ads Manager (obligatorio para atribución)</x-slot>
            <x-slot name="description">
                Usa el ID de campaña de Meta en <code class="text-xs">utm_campaign</code> para cruzar gastos con leads y ventas en el dashboard KPI.
            </x-slot>

            <div class="space-y-4 text-sm text-gray-700 dark:text-gray-300">
                <p>En la URL del anuncio, añade estos parámetros dinámicos:</p>
                <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-xs text-gray-100">{{ $utmTemplate }}</pre>
                <ul class="list-disc space-y-1 pl-5">
                    <li><strong>utm_campaign</strong> = <code>@{{campaign.id}}</code> — debe coincidir con el ID en Meta Ads KPI</li>
                    <li><strong>utm_content</strong> = <code>@{{ad.id}}</code> — opcional, desglose por creativo</li>
                    <li><strong>utm_term</strong> = <code>@{{adset.name}}</code> — nombre del conjunto de anuncios</li>
                    <li>El sitio envía <code>fbp</code> y <code>fbc</code> en checkout; CAPI usa los mismos IDs de evento que el pixel en confirmación</li>
                </ul>
                <p>
                    <a href="https://business.facebook.com" target="_blank" rel="noopener" class="text-primary-600 underline">
                        Abrir Business Manager
                    </a>
                    ·
                    <a href="{{ route('filament.admin.pages.meta-ads-performance') }}" class="text-primary-600 underline">
                        Ir al dashboard Meta Ads KPI
                    </a>
                    ·
                    <a href="{{ route('filament.admin.pages.site-settings') }}" class="text-primary-600 underline">
                        Pixel ID en Configuración del sitio
                    </a>
                </p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Variables de entorno</x-slot>
            <pre class="overflow-x-auto rounded-lg bg-gray-50 p-4 text-xs text-gray-800 dark:bg-white/5 dark:text-gray-200">META_PIXEL_ENABLED=true
META_PIXEL_ID=...
META_CAPI_ENABLED=true
META_ADS_ENABLED=true
META_ACCESS_TOKEN=...
META_AD_ACCOUNT_ID=act_...
META_API_VERSION={{ $apiVersion }}</pre>
        </x-filament::section>
    </div>
</x-filament-panels::page>
