<?php

namespace App\Filament\Resources\GuestListInviteLinks;

use App\Filament\Resources\GuestListInviteLinks\Pages\CreateGuestListInviteLink;
use App\Filament\Resources\GuestListInviteLinks\Pages\EditGuestListInviteLink;
use App\Filament\Resources\GuestListInviteLinks\Pages\ListGuestListInviteLinks;
use App\Filament\Resources\GuestListInviteLinks\Pages\ViewGuestListInviteLink;
use App\Filament\Resources\GuestListInviteLinks\Schemas\GuestListInviteLinkForm;
use App\Filament\Resources\GuestListInviteLinks\Schemas\GuestListInviteLinkInfolist;
use App\Filament\Resources\GuestListInviteLinks\Tables\GuestListInviteLinksTable;
use App\Models\GuestListInviteLink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GuestListInviteLinkResource extends Resource
{
    protected static ?string $model = GuestListInviteLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $modelLabel = 'Link de Invitación';

    protected static ?string $pluralModelLabel = 'Links de Invitación';

    protected static UnitEnum|string|null $navigationGroup = 'Eventos';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return GuestListInviteLinkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GuestListInviteLinkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuestListInviteLinksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\GuestListInviteLinks\RelationManagers\GuestListEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuestListInviteLinks::route('/'),
            'create' => CreateGuestListInviteLink::route('/create'),
            'view' => ViewGuestListInviteLink::route('/{record}'),
            'edit' => EditGuestListInviteLink::route('/{record}/edit'),
        ];
    }
}
