<?php

namespace App\Filament\Resources\PortfolioItems;

use App\Filament\Resources\PortfolioItems\Pages\BulkUploadPortfolioItems;
use App\Filament\Resources\PortfolioItems\Pages\CreatePortfolioItem;
use App\Filament\Resources\PortfolioItems\Pages\EditPortfolioItem;
use App\Filament\Resources\PortfolioItems\Pages\ListPortfolioItems;
use App\Filament\Resources\PortfolioItems\Schemas\PortfolioItemForm;
use App\Filament\Resources\PortfolioItems\Tables\PortfolioItemsTable;
use App\Models\PortfolioItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PortfolioItemResource extends Resource
{
    protected static ?string $model = PortfolioItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Portafolio';

    protected static ?string $modelLabel = 'Pieza';

    protected static ?string $pluralModelLabel = 'Portafolio';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return ! config('trascendental.enabled_as_primary');
    }

    public static function form(Schema $schema): Schema
    {
        return PortfolioItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PortfolioItemsTable::configure($table);
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
            'index' => ListPortfolioItems::route('/'),
            'create' => CreatePortfolioItem::route('/create'),
            'edit' => EditPortfolioItem::route('/{record}/edit'),
            'bulk-upload' => BulkUploadPortfolioItems::route('/bulk-upload'),
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
