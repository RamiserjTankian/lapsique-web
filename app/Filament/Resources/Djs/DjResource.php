<?php

namespace App\Filament\Resources\Djs;

use App\Filament\Resources\Djs\Pages\CreateDj;
use App\Filament\Resources\Djs\Pages\EditDj;
use App\Filament\Resources\Djs\Pages\ListDjs;
use App\Filament\Resources\Djs\Schemas\DjForm;
use App\Filament\Resources\Djs\Tables\DjsTable;
use App\Models\Dj;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DjResource extends Resource
{
    protected static ?string $model = Dj::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

    protected static ?string $navigationLabel = 'Djs';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return DjForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DjsTable::configure($table);
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
            'index' => ListDjs::route('/'),
            'create' => CreateDj::route('/create'),
            'edit' => EditDj::route('/{record}/edit'),
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
