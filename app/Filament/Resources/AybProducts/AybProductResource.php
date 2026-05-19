<?php

namespace App\Filament\Resources\AybProducts;

use App\Filament\Resources\AybProducts\Pages\CreateAybProduct;
use App\Filament\Resources\AybProducts\Pages\EditAybProduct;
use App\Filament\Resources\AybProducts\Pages\ListAybProducts;
use App\Filament\Resources\AybProducts\Schemas\AybProductForm;
use App\Filament\Resources\AybProducts\Tables\AybProductsTable;
use App\Models\AybProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AybProductResource extends Resource
{
    protected static ?string $model = AybProduct::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCake;

    protected static ?string $navigationLabel = 'AyB';

    protected static ?string $modelLabel = 'Producto AyB';

    protected static ?string $pluralModelLabel = 'Productos AyB';

    protected static UnitEnum | string | null $navigationGroup = 'POS';

    protected static ?int $navigationSort = 0;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return AybProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AybProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAybProducts::route('/'),
            'create' => CreateAybProduct::route('/create'),
            'edit' => EditAybProduct::route('/{record}/edit'),
        ];
    }
}
