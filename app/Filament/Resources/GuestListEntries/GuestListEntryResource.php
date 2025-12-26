<?php

namespace App\Filament\Resources\GuestListEntries;

use App\Filament\Resources\GuestListEntries\Pages\CreateGuestListEntry;
use App\Filament\Resources\GuestListEntries\Pages\EditGuestListEntry;
use App\Filament\Resources\GuestListEntries\Pages\ListGuestListEntries;
use App\Filament\Resources\GuestListEntries\Schemas\GuestListEntryForm;
use App\Filament\Resources\GuestListEntries\Tables\GuestListEntriesTable;
use App\Models\GuestListEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class GuestListEntryResource extends Resource
{
    protected static ?string $model = GuestListEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Guest List Entries';

    protected static UnitEnum|string|null $navigationGroup = 'Eventos';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return GuestListEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuestListEntriesTable::configure($table);
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
            'index' => ListGuestListEntries::route('/'),
            'create' => CreateGuestListEntry::route('/create'),
            'edit' => EditGuestListEntry::route('/{record}/edit'),
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
