<?php

namespace App\Filament\Resources\StripeSettings;

use App\Filament\Resources\StripeSettings\Pages\CreateStripeSetting;
use App\Filament\Resources\StripeSettings\Pages\EditStripeSetting;
use App\Filament\Resources\StripeSettings\Pages\ListStripeSettings;
use App\Filament\Resources\StripeSettings\Schemas\StripeSettingForm;
use App\Filament\Resources\StripeSettings\Tables\StripeSettingsTable;
use App\Models\StripeSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StripeSettingResource extends Resource
{
    protected static ?string $model = StripeSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Stripe';

    protected static ?string $modelLabel = 'configuración de Stripe';

    protected static ?string $pluralModelLabel = 'Stripe';

    protected static UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?string $navigationParentItem = 'Configuración';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return StripeSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StripeSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStripeSettings::route('/'),
            'create' => CreateStripeSetting::route('/create'),
            'edit' => EditStripeSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        if (! StripeSetting::tableExists()) {
            return false;
        }

        return StripeSetting::query()->count() === 0;
    }
}
