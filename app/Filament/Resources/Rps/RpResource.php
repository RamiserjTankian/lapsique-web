<?php

namespace App\Filament\Resources\Rps;

use App\Filament\Resources\Rps\Pages\CreateRp;
use App\Filament\Resources\Rps\Pages\EditRp;
use App\Filament\Resources\Rps\Pages\ListRps;
use App\Filament\Resources\Rps\Pages\ViewRp;
use App\Filament\Resources\Rps\Schemas\RpForm;
use App\Filament\Resources\Rps\Schemas\RpInfolist;
use App\Filament\Resources\Rps\Tables\RpsTable;
use App\Models\Rp;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RpResource extends Resource
{
    protected static ?string $model = Rp::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'RP';

    protected static ?string $pluralModelLabel = 'RPs';

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RpForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RpInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RpsTable::configure($table);
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
            'index' => ListRps::route('/'),
            'create' => CreateRp::route('/create'),
            'view' => ViewRp::route('/{record}'),
            'edit' => EditRp::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
