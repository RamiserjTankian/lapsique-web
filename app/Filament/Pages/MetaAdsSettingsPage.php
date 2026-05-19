<?php

namespace App\Filament\Pages;

use App\Models\MetaCampaignDailyInsight;
use App\Models\SiteSetting;
use App\Services\Meta\MetaMarketingApiClient;
use App\Support\Meta;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class MetaAdsSettingsPage extends Page
{
    protected static ?string $navigationLabel = 'Configuración Meta Ads';

    protected static ?string $title = 'Configuración Meta Ads';

    protected static UnitEnum|string|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 4;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public function getView(): string
    {
        return 'filament.pages.meta-ads-settings';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $client = app(MetaMarketingApiClient::class);

        return [
            'marketingConfigured' => $client->isConfigured(),
            'capiEnabled' => Meta::capiEnabled(),
            'pixelEnabled' => Meta::pixelEnabled(),
            'pixelId' => Meta::pixelId(),
            'pixelIdSource' => SiteSetting::metaPixelId()
                ? 'Base de datos (Configuración del sitio)'
                : (config('meta.pixel.id') ? '.env (META_PIXEL_ID)' : 'No configurado'),
            'adAccountId' => config('meta.marketing_api.ad_account_id'),
            'apiVersion' => config('meta.marketing_api.api_version', 'v21.0'),
            'utmTemplate' => config('meta.attribution.utm_template'),
            'lastSyncedAt' => Schema::hasTable('meta_campaign_daily_insights')
                ? MetaCampaignDailyInsight::query()->max('synced_at')
                : null,
        ];
    }
}
